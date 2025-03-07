<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Drupal\Component\Annotation\Doctrine\StaticReflectionParser;
use Drupal\Component\Annotation\Reflection\MockFileFinder;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Core\Extension\ProceduralCall;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\Core\Hook\Attribute\LegacyModuleImplementsAlter;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\Attribute\RemoveHook;
use Drupal\Core\Hook\Attribute\StopProceduralHookScan;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects and registers hook implementations.
 *
 * A hook implementation is a class in a Drupal\modulename\Hook namespace
 * where either the class itself or the methods have a #[Hook] attribute.
 * These classes are automatically registered as autowired services.
 *
 * Services for procedural implementation of hooks are also registered
 * using the ProceduralCall class.
 *
 * Finally, a hook_implementations_map container parameter is added. This
 * contains a mapping from [hook,class,method] to the module name.
 */
class HookCollectorPass implements CompilerPassInterface {

  /**
   * A list of include files.
   *
   * (This is required only for BC.)
   */
  protected array $includes = [];

  /**
   * A list of functions implementing hook_module_implements_alter().
   *
   * (This is required only for BC.)
   */
  protected array $moduleImplementsAlters = [];

  /**
   * A list of functions implementing hook_hook_info().
   *
   * (This is required only for BC.)
   */
  private array $hookInfo = [];

  /**
   * A list of .inc files.
   */
  private array $groupIncludes = [];

  /**
   * Implementations, as module names keyed by hook name and "$class::$method".
   *
   * @var array<string, array<string, string>>
   */
  protected array $implementations = [];

  /**
   * @var array<int, list<\Drupal\Core\Hook\HookOperation>>
   */
  protected array $orderAttributesByPhase = [0 => [], 1 => []];

  /**
   * @var list<\Drupal\Core\Hook\Attribute\RemoveHook>
   */
  protected array $removeHookAttributes = [];

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $collector = static::collectAllHookImplementations($container->getParameter('container.modules'), $container);

    $implementationsByHook = $collector->getFilteredImplementations();

    // Loop over all ReOrderHook attributes and gather order information
    // before registering the hooks. This must happen after all collection,
    // but before registration to ensure this ordering directive takes
    // precedence.
    /** @var list<\Drupal\Core\Hook\HookOperation> $hookOrderOperations */
    $hookOrderOperations = array_merge(...$collector->orderAttributesByPhase);
    $orderExtraTypes = $collector->getOrderExtraTypes($hookOrderOperations);

    $container->register(ProceduralCall::class, ProceduralCall::class)
      ->addArgument($collector->includes);

    // Gather includes for each hook_hook_info group.
    // We store this in $groupIncludes so moduleHandler can ensure the files
    // are included runtime when the hooks are invoked.
    $groupIncludes = [];
    foreach ($collector->hookInfo as $function) {
      foreach ($function() as $hook => $info) {
        if (isset($collector->groupIncludes[$info['group']])) {
          $groupIncludes[$hook] = $collector->groupIncludes[$info['group']];
        }
      }
    }

    $implementationsByHook = static::calculateImplementations(
      $implementationsByHook,
      $collector,
      $orderExtraTypes,
      $hookOrderOperations,
    );

    static::writeImplementationsToContainer($container, $implementationsByHook);

    // Update the module handler definition.
    $definition = $container->getDefinition('module_handler');
    $definition->setArgument('$groupIncludes', $groupIncludes);
    $definition->setArgument('$orderedExtraTypes', $orderExtraTypes);
  }

  /**
   * Gets implementation lists with removals already applied.
   *
   * @return array<string, list<string>>
   *   Implementations, as module names keyed by hook name and
   *   "$class::$method".
   */
  protected function getFilteredImplementations(): array {
    $implementationsByHook = $this->implementations;
    foreach ($this->removeHookAttributes as $removeHook) {
      unset($implementationsByHook[$removeHook->hook][$removeHook->class . '::' . $removeHook->method]);
    }
    return $implementationsByHook;
  }

  /**
   * Gets groups of extra hooks from collected data.
   *
   * @param list<\Drupal\Core\Hook\HookOperation> $hookOrderOperations
   *   All attributes that contain ordering information.
   *
   * @return array<string, list<string>>
   *   Lists of extra hooks keyed by main hook.
   */
  protected function getOrderExtraTypes(array $hookOrderOperations): array {
    // Loop over all ReOrderHook attributes and gather order information
    // before registering the hooks. This must happen after all collection,
    // but before registration to ensure this ordering directive takes
    // precedence.
    /** @var list<\Drupal\Core\Hook\HookOperation> $hookOrderOperations */
    $hookOrderOperations = array_merge(...$this->orderAttributesByPhase);
    $orderExtraTypes = [];
    foreach ($hookOrderOperations as $hookWithOrder) {
      if ($hookWithOrder->order instanceof ComplexOrder && $hookWithOrder->order->extraTypes) {
        $extraTypes = [... $hookWithOrder->order->extraTypes, $hookWithOrder->hook];
        foreach ($extraTypes as $extraHook) {
          $orderExtraTypes[$extraHook] = array_merge($orderExtraTypes[$extraHook] ?? [], $extraTypes);
        }
      }
    }
    $orderExtraTypes = array_map('array_unique', $orderExtraTypes);
    return array_map('array_values', $orderExtraTypes);
  }

  /**
   * Calculates the ordered implementations.
   *
   * @param array<string, array<string, string>> $implementationsByHookOrig
   *   Implementations before ordering, as module names keyed by hook name and
   *   "$class::$method" identifier.
   *   All implementations, as method names keyed by hook, module and class.
   * @param \Drupal\Core\Hook\HookCollectorPass $collector
   *   The collector.
   * @param array<string, list<string>> $orderExtraTypes
   *   Extra types to order a hook with.
   * @param list<\Drupal\Core\Hook\HookOperation> $hookOrderOperations
   *   All attributes that contain ordering information.
   *
   * @return array<string, array<string, string>>
   *   Implementations, as module names keyed by hook name and "$class::$method"
   *   identifier.
   */
  protected static function calculateImplementations(
    array $implementationsByHookOrig,
    self $collector,
    array $orderExtraTypes,
    array $hookOrderOperations,
  ): array {
    // List of hooks and modules formatted for hook_module_implements_alter().
    $moduleImplementsMap = [];
    foreach ($implementationsByHookOrig as $hook => $hookImplementations) {
      foreach ($hookImplementations as $module) {
        $moduleImplementsMap[$hook][$module] = '';
      }
    }

    $implementationsByHook = [];
    foreach ($moduleImplementsMap as $hook => $moduleImplements) {
      $extraHooks = $orderExtraTypes[$hook] ?? [];
      // Add implementations to the array we pass to legacy ordering
      // when the definition specifies that they should be ordered together.
      foreach ($extraHooks as $extraHook) {
        $moduleImplements += $moduleImplementsMap[$extraHook] ?? [];
      }
      // Process all hook_module_implements_alter() for build time ordering.
      foreach ($collector->moduleImplementsAlters as $alter) {
        $alter($moduleImplements, $hook);
      }
      foreach ($moduleImplements as $module => $v) {
        foreach (array_keys($implementationsByHookOrig[$hook], $module, TRUE) as $identifier) {
          $implementationsByHook[$hook][$identifier] = $module;
        }
        if (count($extraHooks) > 1) {
          $combinedHook = implode(':', $extraHooks);
          foreach ($extraHooks as $extraHook) {
            foreach (array_keys($implementationsByHookOrig[$extraHook] ?? [], $module, TRUE) as $identifier) {
              $implementationsByHook[$combinedHook][$identifier] = $module;
            }
          }
        }
      }
    }

    foreach ($hookOrderOperations as $hookOrderOperation) {
      static::applyOrderAttributeOperation(
        $implementationsByHook,
        $orderExtraTypes,
        $hookOrderOperation,
      );
    }

    return $implementationsByHook;
  }

  /**
   * Applies hook order changes from a single attribute with order information.
   *
   * @param array<string, array<string, string>> $implementationsByHook
   *   Implementations, as module names keyed by hook name and "$class::$method"
   *   identifier.
   * @param array<string, list<string>> $orderExtraTypes
   *   Extra types to order a hook with.
   * @param \Drupal\Core\Hook\HookOperation $hookOrderOperation
   *   Hook attribute with order information.
   */
  protected static function applyOrderAttributeOperation(
    array &$implementationsByHook,
    array $orderExtraTypes,
    HookOperation $hookOrderOperation,
  ): void {
    // ::process() adds the hook serving as key to the order extraTypes so it
    // does not need to be added if there's a extraTypes for the hook.
    $hooks = $orderExtraTypes[$hookOrderOperation->hook] ?? [$hookOrderOperation->hook];
    $combinedHook = implode(':', $hooks);
    $identifier = $hookOrderOperation->class . '::' . $hookOrderOperation->method;
    $module = $implementationsByHook[$combinedHook][$identifier] ?? NULL;
    if ($module === NULL) {
      // Implementation is not in the list. Nothing to reorder.
      return;
    }
    $list = $implementationsByHook[$combinedHook];
    $order = $hookOrderOperation->order;
    if ($order === NULL) {
      throw new \InvalidArgumentException('This method must only be called with attributes that have order information.');
    }
    if ($order === Order::First) {
      unset($list[$identifier]);
      $list = [$identifier => $module] + $list;
    }
    elseif ($order === Order::Last) {
      unset($list[$identifier]);
      $list[$identifier] = $module;
    }
    elseif ($order instanceof ComplexOrder) {
      $shouldBeAfter = !$order->value;
      unset($list[$identifier]);
      $identifiers = array_keys($list);
      $modules = array_values($list);
      $compareIndices = [];
      if (isset($hookOrderOperation->order->modules)) {
        $compareIndices = array_keys(array_intersect($modules, $hookOrderOperation->order->modules));
      }
      foreach ($hookOrderOperation->order->classesAndMethods as [$otherClass, $otherMethod]) {
        $compareIndices[] = array_search("$otherClass::$otherMethod", $identifiers, TRUE);
      }
      if (!$compareIndices) {
        return;
      }
      $splice_index = $shouldBeAfter
        ? max($compareIndices) + 1
        : min($compareIndices);
      array_splice($identifiers, $splice_index, 0, [$identifier]);
      array_splice($modules, $splice_index, 0, [$module]);
      $list = array_combine($identifiers, $modules);
    }
    $implementationsByHook[$combinedHook] = $list;
  }

  /**
   * Writes all implementations to the container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container builder.
   * @param array<string, array<string, string>> $implementationsByHook
   *   Implementations, as module names keyed by hook name and "$class::$method"
   *   identifier.
   */
  protected static function writeImplementationsToContainer(
    ContainerBuilder $container,
    array $implementationsByHook,
  ): void {
    $map = [];
    $tagsInfoByClass = [];
    foreach ($implementationsByHook as $hook => $hookImplementations) {
      $priority = 0;
      foreach ($hookImplementations as $class_and_method => $module) {
        [$class, $method] = explode('::', $class_and_method);
        $tagsInfoByClass[$class][] = [
          'event' => "drupal_hook.$hook",
          'method' => $method,
          'priority' => $priority,
        ];
        --$priority;
        $map[$hook][$class][$method] = $module;
      }
    }

    foreach ($tagsInfoByClass as $class => $tagsInfo) {
      if ($container->hasDefinition($class)) {
        $definition = $container->findDefinition($class);
      }
      else {
        $definition = $container
          ->register($class, $class)
          ->setAutowired(TRUE);
      }
      foreach ($tagsInfo as $tag_info) {
        $definition->addTag('kernel.event_listener', $tag_info);
      }
    }

    $container->setParameter('hook_implementations_map', $map);
  }

  /**
   * Collects all hook implementations.
   *
   * @param array $module_filenames
   *   An associative array. Keys are the module names, values are relevant
   *   info yml file path.
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder|null $container
   *   The container.
   *
   * @return static
   *   A HookCollectorPass instance holding all hook implementations and
   *   include file information.
   *
   * @internal
   *   This method is only used by ModuleHandler.
   *
   * @todo Pass only $container when ModuleHandler::add() is removed
   *   @see https://www.drupal.org/project/drupal/issues/3481778
   */
  public static function collectAllHookImplementations(array $module_filenames, ?ContainerBuilder $container = NULL): static {
    $modules = array_map(static fn ($x) => preg_quote($x, '/'), array_keys($module_filenames));
    // Longer modules first.
    usort($modules, fn($a, $b) => strlen($b) - strlen($a));
    $module_preg = '/^(?<function>(?<module>' . implode('|', $modules) . ')_(?!preprocess_)(?!update_\d)(?<hook>[a-zA-Z0-9_\x80-\xff]+$))/';
    $collector = new static();
    foreach ($module_filenames as $module => $info) {
      $skip_procedural = FALSE;
      if ($container?->hasParameter("$module.hooks_converted")) {
        $skip_procedural = $container->getParameter("$module.hooks_converted");
      }
      $collector->collectModuleHookImplementations(dirname($info['pathname']), $module, $module_preg, $skip_procedural);
    }
    return $collector;
  }

  /**
   * Collects procedural and Attribute hook implementations.
   *
   * @param string $dir
   *   The directory in which the module resides.
   * @param string $module
   *   The name of the module.
   * @param string $module_preg
   *   A regular expression matching every module, longer module names are
   *   matched first.
   * @param bool $skip_procedural
   *   Skip the procedural check for the current module.
   */
  protected function collectModuleHookImplementations($dir, $module, $module_preg, bool $skip_procedural): void {
    $hook_file_cache = FileCacheFactory::get('hook_implementations');
    $procedural_hook_file_cache = FileCacheFactory::get('procedural_hook_implementations:' . $module_preg);

    $iterator = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::FOLLOW_SYMLINKS);
    $iterator = new \RecursiveCallbackFilterIterator($iterator, static::filterIterator(...));
    $iterator = new \RecursiveIteratorIterator($iterator);
    /** @var \RecursiveDirectoryIterator | \RecursiveIteratorIterator $iterator*/
    foreach ($iterator as $fileinfo) {
      assert($fileinfo instanceof \SplFileInfo);
      $extension = $fileinfo->getExtension();
      $filename = $fileinfo->getPathname();

      if (($extension === 'module' || $extension === 'profile') && !$iterator->getDepth() && !$skip_procedural) {
        // There is an expectation for all modules and profiles to be loaded.
        // .module and .profile files are not supposed to be in subdirectories.
        // These need to be loaded even if the module has no procedural hooks.
        include_once $filename;
      }
      if ($extension === 'php') {
        $cached = $hook_file_cache->get($filename);
        if ($cached) {
          $class = $cached['class'];
          $attributes = $cached['attributes'];
        }
        else {
          $namespace = preg_replace('#^src/#', "Drupal/$module/", $iterator->getSubPath());
          $class = $namespace . '/' . $fileinfo->getBasename('.php');
          $class = str_replace('/', '\\', $class);
          $attributes = [];
          if (class_exists($class)) {
            $reflectionClass = new \ReflectionClass($class);
            $attributes = self::getAttributeInstances($reflectionClass);
            $hook_file_cache->set($filename, ['class' => $class, 'attributes' => $attributes]);
          }
        }
        foreach ($attributes as $method => $methodAttributes) {
          foreach ($methodAttributes as $attribute) {
            if ($attribute instanceof Hook) {
              self::checkForProceduralOnlyHooks($attribute, $class);
              $this->implementations[$attribute->hook][$class . '::' . ($attribute->method ?: $method)] = $attribute->module ?? $module;
              if ($attribute->order !== NULL) {
                $attribute->set($class, $attribute->module ?? $module, $method);
                $this->orderAttributesByPhase[0][] = $attribute;
              }
            }
            elseif ($attribute instanceof ReOrderHook) {
              $this->orderAttributesByPhase[1][] = $attribute;
            }
            elseif ($attribute instanceof RemoveHook) {
              $this->removeHookAttributes[] = $attribute;
            }
          }
        }
      }
      elseif (!$skip_procedural) {
        $implementations = $procedural_hook_file_cache->get($filename);
        if ($implementations === NULL) {
          $finder = MockFileFinder::create($filename);
          $parser = new StaticReflectionParser('', $finder);
          $implementations = [];
          foreach ($parser->getMethodAttributes() as $function => $attributes) {
            if (StaticReflectionParser::hasAttribute($attributes, StopProceduralHookScan::class)) {
              break;
            }
            if (!StaticReflectionParser::hasAttribute($attributes, LegacyHook::class) && preg_match($module_preg, $function, $matches) && !StaticReflectionParser::hasAttribute($attributes, LegacyModuleImplementsAlter::class)) {
              $implementations[] = ['function' => $function, 'module' => $matches['module'], 'hook' => $matches['hook']];
            }
          }
          $procedural_hook_file_cache->set($filename, $implementations);
        }
        foreach ($implementations as $implementation) {
          $this->addProceduralImplementation($fileinfo, $implementation['hook'], $implementation['module'], $implementation['function']);
        }
      }
      if ($extension === 'inc') {
        $parts = explode('.', $fileinfo->getFilename());
        if (count($parts) === 3 && $parts[0] === $module) {
          $this->groupIncludes[$parts[1]][] = $filename;
        }
      }
    }
  }

  /**
   * Filter iterator callback. Allows include files and .php files in src/Hook.
   */
  protected static function filterIterator(\SplFileInfo $fileInfo, $key, \RecursiveDirectoryIterator $iterator): bool {
    $sub_path_name = $iterator->getSubPathname();
    $extension = $fileInfo->getExtension();
    if (str_starts_with($sub_path_name, 'src/Hook/')) {
      return $iterator->isDir() || $extension === 'php';
    }
    if ($iterator->isDir()) {
      if ($sub_path_name === 'src' || $sub_path_name === 'src/Hook') {
        return TRUE;
      }
      // glob() doesn't support streams but scandir() does.
      return !in_array($fileInfo->getFilename(), ['tests', 'js', 'css']) && !array_filter(scandir($key), fn ($filename) => str_ends_with($filename, '.info.yml'));
    }
    return in_array($extension, ['inc', 'module', 'profile', 'install']);
  }

  /**
   * Adds a procedural hook implementation.
   *
   * @param \SplFileInfo $fileinfo
   *   The file this procedural implementation is in.
   * @param string $hook
   *   The name of the hook.
   * @param string $module
   *   The module of the hook. Note this might be different from the module the
   *   function is in.
   * @param string $function
   *   The name of function implementing the hook.
   */
  protected function addProceduralImplementation(\SplFileInfo $fileinfo, string $hook, string $module, string $function): void {
    if ($hook === 'hook_info') {
      $this->hookInfo[] = $function;
    }
    elseif ($hook === 'module_implements_alter') {
      $message = "$function without a #[LegacyModuleImplementsAlter] attribute is deprecated in drupal:11.2.0 and removed in drupal:12.0.0. See https://www.drupal.org/node/3496788";
      @trigger_error($message, E_USER_DEPRECATED);
      $this->moduleImplementsAlters[] = $function;
    }
    $this->implementations[$hook][ProceduralCall::class . '::' . $module . '_' . $hook] = $module;
    if ($fileinfo->getExtension() !== 'module') {
      $this->includes[$function] = $fileinfo->getPathname();
    }
  }

  /**
   * This method is only to be used by ModuleHandler.
   *
   * @todo remove when ModuleHandler::add() is removed.
   * @see https://www.drupal.org/project/drupal/issues/3481778
   *
   * @internal
   */
  public function loadAllIncludes(): void {
    foreach ($this->includes as $include) {
      include_once $include;
    }
  }

  /**
   * This method is only to be used by ModuleHandler.
   *
   * @todo remove when ModuleHandler::add() is removed.
   *   See https://www.drupal.org/project/drupal/issues/3481778
   *
   * @internal
   */
  public function getImplementations(array $paths): array {
    $implementationsByHook = $this->getFilteredImplementations();

    // List of modules implementing hooks with the implementation details.
    $implementations = [];

    $modules = array_keys($paths);
    foreach ($implementationsByHook as $hook => $hookImplementations) {
      foreach ($modules as $module) {
        foreach (array_keys($hookImplementations, $module, TRUE) as $identifier) {
          [$class, $method] = explode('::', $identifier);
          $implementations[$hook][$module][$class][$method] = $method;
        }
      }
    }

    return $implementations;
  }

  /**
   * Checks for hooks which can't be supported in classes.
   *
   * @param \Drupal\Core\Hook\Attribute\Hook $hookAttribute
   *   The hook to check.
   * @param class-string $class
   *   The class the hook is implemented on.
   */
  public static function checkForProceduralOnlyHooks(Hook $hookAttribute, string $class): void {
    $staticDenyHooks = [
      'hook_info',
      'install',
      'module_implements_alter',
      'requirements',
      'schema',
      'uninstall',
      'update_last_removed',
      'install_tasks',
      'install_tasks_alter',
    ];

    if (in_array($hookAttribute->hook, $staticDenyHooks) || preg_match('/^(post_update_|preprocess_|update_\d+$)/', $hookAttribute->hook)) {
      throw new \LogicException("The hook $hookAttribute->hook on class $class does not support attributes and must remain procedural.");
    }
  }

  /**
   * Get attribute instances from class and method reflections.
   *
   * @param \ReflectionClass $reflectionClass
   *   A reflected class.
   *
   * @return array<string, list<\Drupal\Core\Hook\HookOperation>>
   *   Lists of Hook attribute instances by method name.
   */
  protected static function getAttributeInstances(\ReflectionClass $reflectionClass): array {
    $attributes = [];
    $reflections = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    $reflections[] = $reflectionClass;
    foreach ($reflections as $reflection) {
      if ($reflectionAttributes = $reflection->getAttributes(HookOperation::class, \ReflectionAttribute::IS_INSTANCEOF)) {
        $method = $reflection instanceof \ReflectionMethod ? $reflection->getName() : '__invoke';
        $attributes[$method] = array_map(static fn (\ReflectionAttribute $ra) => $ra->newInstance(), $reflectionAttributes);
      }
    }
    return $attributes;
  }

}
