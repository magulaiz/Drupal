<?php

namespace Drupal\Core\Extension;

use Drupal\Component\Event\ResetEvent;
use Drupal\Component\Graph\Graph;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\Hook\HookMapInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class that manages modules in a Drupal installation.
 */
class ModuleHandler implements ModuleHandlerInterface {

  /**
   * List of loaded files.
   *
   * @var array<string, true>
   *   An associative array whose keys are file paths of loaded files, relative
   *   to the application's root directory.
   */
  protected $loadedFiles;

  /**
   * List of installed modules.
   *
   * @var \Drupal\Core\Extension\Extension[]
   */
  protected $moduleList;

  /**
   * Boolean indicating whether modules have been loaded.
   *
   * @var bool
   */
  protected $loaded = FALSE;

  /**
   * A list of module include file keys.
   *
   * @var array<string, string|false>
   */
  protected $includeFileKeys = [];

  /**
   * Constructs a ModuleHandler object.
   *
   * @param string $root
   *   The app root.
   * @param array<string, array> $module_list
   *   An associative array whose keys are the names of installed modules and
   *   whose values are Extension class parameters. This is normally the
   *   %container.modules% parameter being set up by DrupalKernel.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend for storing module hook implementation information.
   * @param \Drupal\Core\Extension\Hook\HookMapInterface $hookMap
   *   Hook map.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Cache tags invalidator.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   *
   * @see \Drupal\Core\DrupalKernel
   * @see \Drupal\Core\CoreServiceProvider
   */
  public function __construct(
    #[Autowire('%app.root%')]
    protected readonly string $root,
    #[Autowire('%container.modules%')]
    array $module_list,
    #[Autowire('@cache.bootstrap')]
    protected readonly CacheBackendInterface $cacheBackend,
    protected readonly HookMapInterface $hookMap,
    protected readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    protected readonly EventDispatcherInterface $eventDispatcher,
  ) {
    $this->moduleList = [];
    foreach ($module_list as $name => $module) {
      $this->moduleList[$name] = new Extension($this->root, $module['type'], $module['pathname'], $module['filename']);
    }
    $hookMap->setModuleHandler($this);
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    if (isset($this->loadedFiles[$name])) {
      return TRUE;
    }

    if (isset($this->moduleList[$name])) {
      $this->moduleList[$name]->load();
      $this->loadedFiles[$name] = TRUE;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function loadAll() {
    if (!$this->loaded) {
      foreach ($this->moduleList as $name => $module) {
        $this->load($name);
      }
      $this->loaded = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reload() {
    $this->loaded = FALSE;
    $this->loadAll();
  }

  /**
   * {@inheritdoc}
   */
  public function isLoaded() {
    return $this->loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleList() {
    return $this->moduleList;
  }

  /**
   * {@inheritdoc}
   */
  public function getModule($name) {
    if (isset($this->moduleList[$name])) {
      return $this->moduleList[$name];
    }
    throw new UnknownExtensionException(sprintf('The module %s does not exist.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function setModuleList(array $module_list = []) {
    assert(array_keys($module_list) === array_map(
      static fn (Extension $module): string => $module->getName(),
      array_values($module_list),
    ));
    $this->moduleList = $module_list;
    $this->eventDispatcher->dispatch(
      new ResetEvent(),
      ExtensionEvents::MODULE_LIST_WAS_UPDATED,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addModule($name, $path) {
    $this->add('module', $name, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function addProfile($name, $path) {
    $this->add('profile', $name, $path);
  }

  /**
   * Adds a module or profile to the list of currently active modules.
   *
   * @param string $type
   *   The extension type; either 'module' or 'profile'.
   * @param string $name
   *   The module name; e.g., 'node'.
   * @param string $path
   *   The module path; e.g., 'core/modules/node'.
   */
  protected function add($type, $name, $path) {
    $pathname = "$path/$name.info.yml";
    $filename = file_exists($this->root . "/$path/$name.$type") ? "$name.$type" : NULL;
    $this->moduleList[$name] = new Extension($this->root, $type, $pathname, $filename);
    $this->eventDispatcher->dispatch(
      new ResetEvent(),
      ExtensionEvents::MODULE_LIST_WAS_UPDATED,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildModuleDependencies(array $modules) {
    foreach ($modules as $module) {
      $graph[$module->getName()]['edges'] = [];
      if (isset($module->info['dependencies']) && is_array($module->info['dependencies'])) {
        foreach ($module->info['dependencies'] as $dependency) {
          $dependency_data = Dependency::createFromString($dependency);
          $graph[$module->getName()]['edges'][$dependency_data->getName()] = $dependency_data;
        }
      }
    }
    $graph_object = new Graph($graph ?? []);
    $graph = $graph_object->searchAndSort();
    foreach ($graph as $module_name => $data) {
      $modules[$module_name]->required_by = $data['reverse_paths'] ?? [];
      $modules[$module_name]->requires = $data['paths'] ?? [];
      $modules[$module_name]->sort = $data['weight'];
    }
    return $modules;
  }

  /**
   * {@inheritdoc}
   */
  public function moduleExists($module) {
    return isset($this->moduleList[$module]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllIncludes($type, $name = NULL) {
    foreach ($this->moduleList as $module => $filename) {
      $this->loadInclude($module, $type, $name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadInclude($module, $type, $name = NULL) {
    if ($type === 'install') {
      // Make sure the installation API is available
      include_once $this->root . '/core/includes/install.inc';
    }

    $name = $name ?: $module;
    $key = $type . ':' . $module . ':' . $name;
    if (isset($this->includeFileKeys[$key])) {
      return $this->includeFileKeys[$key];
    }
    if (isset($this->moduleList[$module])) {
      $file = $this->root . '/' . $this->moduleList[$module]->getPath() . "/$name.$type";
      if (is_file($file)) {
        require_once $file;
        $this->includeFileKeys[$key] = $file;
        return $file;
      }
      else {
        $this->includeFileKeys[$key] = FALSE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getHookInfo() {
    return $this->hookMap->getHookInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function writeCache() {
    // Do nothing.
    // This method used to be called on a request termination event, and it used
    // to write collected implementations to the cache.
    // Now this same functionality is done in HookMap instead.
    // The method is still here, because it is part of the interface.
    $this->eventDispatcher->dispatch(
      new ResetEvent(),
      ModuleHandlerInterface::class . '::writeCache',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resetImplementations() {
    $this->eventDispatcher->dispatch(
      new ResetEvent(),
      ExtensionEvents::HOOKS_REBUILD_REQUESTED,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasImplementations(string $hook, $modules = NULL): bool {
    return $this->hookMap->hasImplementations($hook, $modules);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllWith(string $hook, callable $callback): void {
    $this->hookMap->getCallbackList($hook)->invokeAllWith($callback);
  }

  /**
   * {@inheritdoc}
   */
  public function invoke($module, $hook, array $args = []): mixed {
    if (!isset($this->moduleList[$module])) {
      // The module is not installed, so the implementation won't be part of the
      // list. Instead, just call the function, if it exists.
      $function = $module . '_' . $hook;
      if (!function_exists($function)) {
        return NULL;
      }
      return $function(...$args);
    }
    return $this->hookMap
      ->getSingleModuleCallbackList($hook, $module)
      ->invoke($args);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAll($hook, array $args = []) {
    return $this->hookMap->getCallbackList($hook)->invokeAll($args);
  }

  /**
   * {@inheritdoc}
   */
  public function invokeDeprecated($description, $module, $hook, array $args = []) {
    $result = $this->invoke($module, $hook, $args);
    $this->triggerDeprecationError($description, $hook);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function invokeAllDeprecated($description, $hook, array $args = []) {
    $result = $this->invokeAll($hook, $args);
    $this->triggerDeprecationError($description, $hook);
    return $result;
  }

  /**
   * Triggers an E_USER_DEPRECATED error if any module implements the hook.
   *
   * @param string $description
   *   Helpful text describing what to do instead of implementing this hook.
   * @param string $hook
   *   The name of the hook.
   */
  private function triggerDeprecationError($description, $hook) {
    $names = $this->hookMap->getPrintableNames($hook);
    if (!$names) {
      return;
    }
    $names = array_map(static fn (string $name) => $name . '()', $names);
    @trigger_error(sprintf(
      'The deprecated hook hook_%s() is implemented in these functions: %s. %s',
      $hook,
      implode(', ', $names),
      $description,
    ), E_USER_DEPRECATED);
  }

  /**
   * {@inheritdoc}
   */
  public function alter($type, &$data, &$context1 = NULL, &$context2 = NULL): void {
    if (is_string($type)) {
      $callback_list = $this->hookMap->getCallbackList($type . '_alter');
    }
    else {
      $hooks = array_map(static fn (string $type) => $type . '_alter', $type);
      $callback_list = $this->hookMap->getCallbackList(...$hooks);
    }
    $callback_list->invokeAllAlter($data, $context1, $context2);
  }

  /**
   * {@inheritdoc}
   */
  public function alterDeprecated($description, $type, &$data, &$context1 = NULL, &$context2 = NULL) {
    // Invoke the alter hook.
    $this->alter($type, $data, $context1, $context2);

    // Trigger a deprecation warning.
    $hooks = array_map(static fn (string $type) => $type . '_alter', (array) $type);
    $names = $this->hookMap->getPrintableNames(...$hooks);
    if (!$names) {
      // No implementations exist, no warning needed.
      return;
    }
    @trigger_error(sprintf(
      'The deprecated alter hook hook_%s() is implemented in these functions: %s. %s',
      reset($hooks),
      implode(', ', $names),
      $description,
    ), E_USER_DEPRECATED);
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDirectories() {
    $dirs = [];
    foreach ($this->getModuleList() as $name => $module) {
      $dirs[$name] = $this->root . '/' . $module->getPath();
    }
    return $dirs;
  }

  /**
   * {@inheritdoc}
   */
  public function getName($module) {
    return \Drupal::service('extension.list.module')->getName($module);
  }

}
