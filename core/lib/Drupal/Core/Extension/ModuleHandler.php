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
   * @param \Drupal\Core\Extension\WritableActiveModuleListInterface $activeModuleList
   *   List of active modules.
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
    protected readonly WritableActiveModuleListInterface $activeModuleList,
    #[Autowire('@cache.bootstrap')]
    protected readonly CacheBackendInterface $cacheBackend,
    protected readonly HookMapInterface $hookMap,
    protected readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    protected readonly EventDispatcherInterface $eventDispatcher,
  ) {
    $hookMap->setModuleHandler($this);
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    if (isset($this->loadedFiles[$name])) {
      return TRUE;
    }

    if ($module_object = $this->activeModuleList->getModule($name)) {
      $module_object->load();
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
      foreach ($this->activeModuleList->getModules() as $name => $module) {
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
    return $this->activeModuleList->getModules();
  }

  /**
   * {@inheritdoc}
   */
  public function getModule($name) {
    return $this->activeModuleList->getModule($name)
      ?? throw new UnknownExtensionException(sprintf('The module %s does not exist.', $name));
  }

  /**
   * {@inheritdoc}
   */
  public function setModuleList(array $module_list = []) {
    $this->activeModuleList->setModules($module_list);
  }

  /**
   * {@inheritdoc}
   */
  public function addModule($name, $path) {
    $this->activeModuleList->addModule($name, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function addProfile($name, $path) {
    $this->activeModuleList->addProfile($name, $path);
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
    return $this->activeModuleList->moduleExists($module);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllIncludes($type, $name = NULL) {
    foreach ($this->activeModuleList->getModules() as $module => $filename) {
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
    if ($module_object = $this->activeModuleList->getModule($module)) {
      $file = $this->root . '/' . $module_object->getPath() . "/$name.$type";
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
    if (!$this->activeModuleList->moduleExists($module)) {
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
    foreach ($this->activeModuleList->getModules() as $name => $module) {
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
