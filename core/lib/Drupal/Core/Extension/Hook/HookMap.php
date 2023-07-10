<?php

namespace Drupal\Core\Extension\Hook;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ExtensionEvents;
use Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder;
use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface;
use Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;
use Drupal\Core\Extension\Hook\Source\ImplementationSourceInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Map of hook implementations.
 */
class HookMap implements HookMapInterface, EventSubscriberInterface {

  const CACHE_ID = 'module_implements_cacheable';

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private ModuleHandlerInterface $moduleHandler;

  /**
   * Information returned by hook_hook_info() implementations.
   *
   * @var array<string, array>|null
   */
  private ?array $hookInfo = NULL;

  /**
   * Module indices by module name.
   *
   * @var array<string, int>
   */
  private array $moduleNumbers;

  /**
   * Structured lists by hook name.
   *
   * @var array<string, \Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder>
   */
  private array $listBuilders = [];

  /**
   * Cacheable lists by hook name(s).
   *
   * @var array<string, \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface>
   */
  private ?array $compactLists = NULL;

  /**
   * Callback lists by hook name(s).
   *
   * @var array<string, \Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackListInterface>
   */
  private array $callbackLists = [];

  /**
   * Callbacks by hook and module.
   *
   * @var array<string, array<string, \Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface>>
   */
  private array $singleModuleCallbackLists = [];

  /**
   * Whether the cache needs to be written.
   *
   * @var bool
   */
  private bool $cacheNeedsWriting = FALSE;

  /**
   * Callback to set the $cacheNeedsWriting value.
   *
   * @var \Closure&(callable(): void)|null
   */
  private ?\Closure $invalidate;

  /**
   * List of service method implementations for all hooks.
   *
   * @var list<array{
   *   module: non-empty-string,
   *   function?: callable-string,
   *   class?: class-string,
   *   service?: non-empty-string,
   *   method?: non-empty-string,
   *   weight?: int,
   *   before?: non-empty-string|list<non-empty-string>,
   *   after?: non-empty-string|list<non-empty-string>,
   * }>|null
   */
  private ?array $serviceMethodImplementations = NULL;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container.
   * @param \Drupal\Core\Extension\Hook\Source\ImplementationSourceInterface $implementationSource
   *   Source of non-procedural implementations.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   */
  public function __construct(
    #[Autowire('@cache.bootstrap')]
    private readonly CacheBackendInterface $cacheBackend,
    private readonly ContainerInterface $container,
    private readonly ImplementationSourceInterface $implementationSource,
    EventDispatcherInterface $eventDispatcher,
  ) {
    $this->invalidate = function (): void {
      $this->cacheNeedsWriting = TRUE;
    };
    $eventDispatcher->addListener(KernelEvents::TERMINATE, $this->writeCache(...));
    // Provide legacy support for ModuleHandler->writeCache().
    $eventDispatcher->addListener(ModuleHandlerInterface::class . '::writeCache', $this->writeCache(...));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [
      ExtensionEvents::MODULE_LIST_WAS_UPDATED => 'reset',
      ExtensionEvents::HOOKS_REBUILD_REQUESTED => 'reset',
    ];
    return $events;
  }

  /**
   * Sets the module handler.
   *
   * This setter is required.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   *
   * @todo Split up the module handler, to avoid this circular dependency.
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler): void {
    $this->moduleHandler = $module_handler;
    $this->moduleNumbers = array_flip(array_keys($module_handler->getModuleList()));
  }

  /**
   * Resets implementations.
   */
  public function reset(): void {
    $this->hookInfo = NULL;
    $this->listBuilders = [];
    $this->compactLists = [];
    $this->callbackLists = [];
    $this->singleModuleCallbackLists = [];
    $this->serviceMethodImplementations = NULL;
    $this->cacheNeedsWriting = FALSE;
    $this->moduleNumbers = array_flip(array_keys($this->moduleHandler->getModuleList()));
    $this->cacheBackend->delete(self::CACHE_ID);
    $this->cacheBackend->delete('hook_info');
  }

  /**
   * Writes hook implementation info to the cache.
   *
   * @see \Drupal\Core\Extension\ModuleHandlerInterface::writeCache()
   */
  public function writeCache(): void {
    if ($this->cacheNeedsWriting) {
      assert($this->compactLists !== NULL);
      $this->cacheBackend->set(self::CACHE_ID, $this->compactLists);
      $this->cacheNeedsWriting = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasImplementations(string $hook, string|array $modules = NULL): bool {
    if ($modules !== NULL) {
      foreach ((array) $modules as $module) {
        // Hook implementations usually found in a module's .install file are
        // not stored in the implementation info cache. In order to invoke hooks
        // like hook_schema() and hook_requirements() the module's .install file
        // must be included by the calling code. Additionally, this check avoids
        // unnecessary work when a hook implementation is present in a module's
        // .module file.
        if (function_exists($module . '_' . $hook)) {
          return TRUE;
        }
      }
    }
    return $this->getCacheableList($hook)->hasImplementations($modules);
  }

  /**
   * {@inheritdoc}
   */
  public function getPrintableNames(string ...$hooks): array {
    return $this->getCacheableList(...$hooks)->getPrintableNames(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function getSingleModuleCallbackList(string $hook, string $module): SingleModuleCallbackListInterface {
    /** @var \Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface|null $ref */
    $ref =& $this->singleModuleCallbackLists[$hook][$module];
    $ref ??= $this->getCacheableList($hook)->buildSingleModuleCallbackList(
      $module,
      $this->container,
      $this->moduleHandler,
      $this->invalidate,
    );
    if ($ref->containsMainFunction() || !function_exists($module . '_' . $hook)) {
      return $ref;
    }
    // The main function is not included in the list of implementations.
    // This can have two reasons:
    // - The file that contains the function was not included when the
    //   implementations were discovered. This is especially relevant for
    //   *.install files.
    // - The function was removed by hook_module_implements_alter(), or some
    //   other mechanism.
    // Either way, the implementation list for this hook can no longer be
    // trusted, and has to be discovered again.
    unset($this->listBuilders[$hook]);
    unset($this->compactLists[$hook]);
    $ref = $this->getCacheableList($hook)->buildSingleModuleCallbackList(
      $module,
      $this->container,
      $this->moduleHandler,
      $this->invalidate,
    );
    return $ref;
  }

  /**
   * {@inheritdoc}
   */
  public function getCallbackList(string $hook, string ...$extra_hooks): HookImplementationCallbackListInterface {
    if (!$extra_hooks) {
      return $this->callbackLists[$hook]
        ??= $this->getCacheableList($hook)->buildCallbackList(
          $this->container,
          $this->moduleHandler,
          $this->invalidate,
        );
    }
    $cid = $hook . '.' . implode('.', $extra_hooks);
    return $this->callbackLists[$cid]
      ??= $this->getCacheableList($hook, ...$extra_hooks)->buildCallbackList(
        $this->container,
        $this->moduleHandler,
        $this->invalidate,
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableList(string $hook, string ...$extra_hooks): CompactImplementationListInterface {
    $cid = $hook;
    if ($extra_hooks) {
      $cid .= '.' . implode('.', $extra_hooks);
    }
    if ($this->compactLists === NULL) {
      try {
        $prev = set_error_handler(static function (...$args) use (&$prev) {
          $prev(...$args);
        });
        $cache = $this->cacheBackend->get(self::CACHE_ID);
      }
      finally {
        restore_error_handler();
      }
      if ($cache && $cache->data !== NULL) {
        assert((static fn (CompactImplementationListInterface ...$lists) => TRUE)(...array_values($cache->data)));
        $this->compactLists = $cache->data;
      }
      else {
        $this->compactLists = [];
      }
    }
    if (!isset($this->compactLists[$cid])) {
      $this->compactLists[$cid] = $this->getListBuilder($hook, ...$extra_hooks)
        ->build();
      $this->cacheNeedsWriting = TRUE;
    }
    return $this->compactLists[$cid];
  }

  /**
   * Gets an object with structured implementation info.
   *
   * @param string $hook
   *   Hook name.
   * @param string ...$extra_hooks
   *   More hook names.
   *
   * @return \Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder
   *   Object with structured implementation info.
   */
  private function getListBuilder(string $hook, string ...$extra_hooks): ImplementationListBuilder {
    $list = $this->listBuilders[$hook]
      ??= $this->buildStructuredImplementationList($hook);
    if (!$extra_hooks) {
      return $list;
    }
    $list = clone $list;
    foreach ($extra_hooks as $extra_hook) {
      $list->merge($this->getListBuilder($extra_hook));
    }
    $list->setOrder($this->moduleNumbers);
    if ($hook !== 'module_implements_alter') {
      $alterable = $list->getAlterable();
      $altered = $alterable;
      $this->getCallbackList('module_implements_alter')->invokeAllAlter($altered, $hook);
      // @todo Add new functions etc.
      $list->setAlteredOrder($altered);
    }
    return $list;
  }

  /**
   * Builds a structured implementation list object.
   *
   * @param string $hook
   *   Hook name.
   *
   * @return \Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder
   *   Value object with implementation info.
   */
  protected function buildStructuredImplementationList(string $hook): ImplementationListBuilder {
    $list = new ImplementationListBuilder($hook);

    // Add procedural implementations.
    foreach ($this->findProceduralImplementations($hook) as $module => $group) {
      $list->addProcedural($module, $group);
    }

    // Add service method and static method implementations.
    foreach ($this->getServiceMethodImplementations($hook) as $info) {
      $list->addImplementation(...$info);
    }

    $list->setOrder($this->moduleNumbers);

    if ($hook !== 'module_implements_alter') {
      $alterable = $altered = $list->getAlterable();
      $this->getCallbackList('module_implements_alter')->invokeAllAlter($altered, $hook);
      foreach (array_filter($altered) as $module => $group) {
        assert(isset($this->moduleNumbers[$module]));
        $this->moduleHandler->loadInclude($module, 'inc', $module . '.' . $group);
        $list->addIncludeFileGroup($module, $group);
      }
      foreach (array_diff_key($altered, $alterable) as $prefix => $group) {
        $function = $prefix . '_' . $hook;
        if (!function_exists($function)) {
          throw new \RuntimeException("An invalid implementation $function was added by hook_module_implements_alter()");
        }
        $list->addImplementation($prefix, function: $function);
      }
      $list->setAlteredOrder($altered);
    }

    return $list;
  }

  /**
   * Finds procedural implementations.
   *
   * @param string $hook
   *   Hook name.
   *
   * @return array<string, string|false>
   *   Group by module name.
   */
  protected function findProceduralImplementations(string $hook): array {
    $default_group = $this->getHookInfo()[$hook]['group'] ?? FALSE;
    $implementations = [];
    foreach ($this->moduleNumbers as $module => $number) {
      $has_include_file = ($default_group !== FALSE)
        && $this->moduleHandler->loadInclude($module, 'inc', $module . '.' . $default_group);
      $function = $module . '_' . $hook;
      if (function_exists($function)) {
        $implementations[$module] = $has_include_file ? $default_group : FALSE;
      }
    }
    return $implementations;
  }

  /**
   * Gets service method implementations.
   *
   * @param string $hook
   *   Hook name.
   *
   * @return array[]
   *   Info about service method implementations.
   *
   * @phpstan-return list<array{
   *   module: non-empty-string,
   *   function?: callable-string,
   *   class?: class-string,
   *   service?: non-empty-string,
   *   method?: non-empty-string,
   *   weight?: int,
   *   before?: non-empty-string|list<non-empty-string>,
   *   after?: non-empty-string|list<non-empty-string>,
   * }>
   */
  private function getServiceMethodImplementations(string $hook): array {
    return ($this->serviceMethodImplementations
      ??= $this->implementationSource->getImplementations())[$hook] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getHookInfo() {
    if ($this->hookInfo === NULL) {
      if ($cache = $this->cacheBackend->get('hook_info')) {
        $this->hookInfo = $cache->data;
      }
      else {
        $this->buildHookInfo();
        $this->cacheBackend->set('hook_info', $this->hookInfo);
      }
    }
    return $this->hookInfo;
  }

  /**
   * Builds hook_hook_info() information.
   *
   * @see \Drupal\Core\Extension\ModuleHandlerInterface::getHookInfo()
   */
  protected function buildHookInfo() {
    $this->hookInfo = [];
    // Make sure that the modules are loaded before checking.
    $this->moduleHandler->reload();
    // $this->invokeAll() would cause an infinite recursion.
    foreach ($this->moduleHandler->getModuleList() as $module => $filename) {
      $function = $module . '_hook_info';
      if (function_exists($function)) {
        $result = $function();
        if (isset($result) && is_array($result)) {
          $this->hookInfo = NestedArray::mergeDeep($this->hookInfo, $result);
        }
      }
    }
  }

}
