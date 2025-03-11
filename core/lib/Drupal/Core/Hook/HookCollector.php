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
use Drupal\Core\Hook\Attribute\RemoveHook;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\Attribute\StopProceduralHookScan;
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
class HookCollector {

  /**
   * A map of include files by function name.
   *
   * (This is required only for BC.)
   *
   * @var array<string, string>
   */
  protected array $includes = [];

  /**
   * A list of functions implementing hook_module_implements_alter().
   *
   * (This is required only for BC.)
   *
   * @var list<callable-string>
   */
  protected array $moduleImplementsAlters = [];

  /**
   * A list of functions implementing hook_hook_info().
   *
   * (This is required only for BC.)
   *
   * @var list<callable-string>
   */
  private array $hookInfo = [];

  /**
   * Include files, keyed by the $group part of "/$module.$group.inc".
   *
   * @var array<string, list<string>>
   */
  private array $groupIncludes = [];

  /**
   * OOP implementation module names keyed by hook name and "$class::$method".
   *
   * @var array<string, array<string, string>>
   */
  protected array $oopImplementations = [];

  /**
   * Procedural implementation module names by hook name.
   *
   * @var array<string, list<string>>
   */
  protected array $proceduralImplementations = [];

  /**
   * Order operations grouped by hook name and weight.
   *
   * Operations with higher weight are applied last, which means they can
   * override the changes from previous operations.
   *
   * @var array<string, array<int, list<\Drupal\Core\Hook\OrderOperation\OrderOperationInterface>>>
   *
   * @todo Review how to combine operations from different hooks.
   */
  protected array $orderOperations = [];

  /**
   * Identifiers to remove, as "$class::$method", keyed by hook name.
   *
   * @var array<string, list<string>>
   */
  protected array $removeHookIdentifiers = [];

  /**
   * Constructor. Should not be called directly.
   *
   * @param list<string> $modules
   *   Names of installed modules.
   */
  protected function __construct(
    protected readonly array $modules,
  ) {}

  /**
   * Writes collected definitions to the container builder.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   Container builder.
   */
  public function writeToContainer(ContainerBuilder $container): void {
    $container->register(ProceduralCall::class, ProceduralCall::class)
      ->addArgument($this->includes);

    // Gather includes for each hook_hook_info group.
    // We store this in $groupIncludes so moduleHandler can ensure the files
    // are included runtime when the hooks are invoked.
    $groupIncludes = [];
    foreach ($this->hookInfo as $function) {
      foreach ($function() as $hook => $info) {
        if (isset($this->groupIncludes[$info['group']])) {
          $groupIncludes[$hook] = $this->groupIncludes[$info['group']];
        }
      }
    }

    $implementationsByHook = $this->calculateImplementations();

    static::writeImplementationsToContainer($container, $implementationsByHook);

    // Update the module handler definition.
    $definition = $container->getDefinition('module_handler');
    $definition->setArgument('$groupIncludes', $groupIncludes);
    $definition->setArgument('$serialized_ordering_rules', serialize($this->getOrderOperations()));
  }

  /**
   * Gets implementation lists with removals already applied.
   *
   * @return array<string, list<string>>
   *   Implementations, as module names keyed by hook name and
   *   "$class::$method".
   */
  protected function getFilteredImplementations(): array {
    $implementationsByHook = [];
    foreach ($this->proceduralImplementations as $hook => $procedural_modules) {
      foreach ($procedural_modules as $module) {
        $implementationsByHook[$hook][ProceduralCall::class . '::' . $module . '_' . $hook] = $module;
      }
    }
    foreach ($this->oopImplementations as $hook => $oopImplementations) {
      if (!isset($implementationsByHook[$hook])) {
        $implementationsByHook[$hook] = $oopImplementations;
      }
      else {
        $implementationsByHook[$hook] += $oopImplementations;
      }
    }
    foreach ($this->removeHookIdentifiers as $hook => $identifiers_to_remove) {
      foreach ($identifiers_to_remove as $identifier_to_remove) {
        unset($implementationsByHook[$hook][$identifier_to_remove]);
      }
      if (empty($implementationsByHook[$hook])) {
        unset($implementationsByHook[$hook]);
      }
    }
    return $implementationsByHook;
  }

  /**
   * Calculates the ordered implementations.
   *
   * @return array<string, array<string, string>>
   *   Implementations, as module names keyed by hook name and "$class::$method"
   *   identifier.
   */
  protected function calculateImplementations(): array {
    $implementationsByHookOrig = $this->getFilteredImplementations();

    // List of hooks and modules formatted for hook_module_implements_alter().
    $moduleImplementsMap = [];
    foreach ($implementationsByHookOrig as $hook => $hookImplementations) {
      foreach (array_intersect($this->modules, $hookImplementations) as $module) {
        $moduleImplementsMap[$hook][$module] = '';
      }
    }

    $implementationsByHook = [];
    foreach ($moduleImplementsMap as $hook => $moduleImplements) {
      // Process all hook_module_implements_alter() for build time ordering.
      foreach ($this->moduleImplementsAlters as $alter) {
        $alter($moduleImplements, $hook);
      }
      foreach ($moduleImplements as $module => $v) {
        foreach (array_keys($implementationsByHookOrig[$hook], $module, TRUE) as $identifier) {
          $implementationsByHook[$hook][$identifier] = $module;
        }
      }
    }

    foreach ($this->getOrderOperations() as $hook => $order_operations) {
      self::applyOrderOperations($implementationsByHook[$hook], $order_operations);
    }

    return $implementationsByHook;
  }

  /**
   * Gets order operations by hook.
   *
   * @return array<string, list<\Drupal\Core\Hook\OrderOperation\OrderOperationInterface>>
   *   Order operations by hook name.
   */
  protected function getOrderOperations(): array {
    $operations_by_hook = [];
    foreach ($this->orderOperations as $hook => $order_operations_by_weight) {
      ksort($order_operations_by_weight);
      $operations_by_hook[$hook] = array_merge(...$order_operations_by_weight);
    }
    return $operations_by_hook;
  }

  /**
   * Applies order operations to a hook implementation list.
   *
   * @param array<string, string> $implementation_list
   *   Implementation list for one hook, as module names keyed by
   *   "$class::$method" identifiers.
   * @param list<\Drupal\Core\Hook\OrderOperation\OrderOperationInterface> $order_operations
   *   A list of order operations for one hook.
   */
  public static function applyOrderOperations(array &$implementation_list, array $order_operations): void {
    $module_finder = $implementation_list;
    $identifiers = array_keys($module_finder);
    foreach ($order_operations as $order_operation) {
      $order_operation->apply($identifiers, $module_finder);
      assert($identifiers === array_unique($identifiers));
      $identifiers = array_values($identifiers);
    }
    // Clean up after bad order operations.
    $identifiers = array_combine($identifiers, $identifiers);
    $identifiers = array_intersect_key($identifiers, $module_finder);
    $implementation_list = array_replace($identifiers, $module_finder);
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
   * @param array<string, array{pathname: string}> $module_list
   *   An associative array. Keys are the module names, values are relevant
   *   info yml file path.
   * @param list<string> $skipProceduralModules
   *   Module names that are known to not have procedural hook implementations.
   *
   * @return static
   *   A HookCollectorPass instance holding all hook implementations and
   *   include file information.
   *
   * @internal
   *   This method is only used by ModuleHandler.
   *
   * @todo Pass only $container when ModuleHandler::add() is removed
   * @see https://www.drupal.org/project/drupal/issues/3481778
   */
  public static function collectAllHookImplementations(array $module_list, array $skipProceduralModules = []): static {
    $modules = array_keys($module_list);
    $modules_by_length = $modules;
    usort($modules_by_length, fn ($a, $b) => strlen($b) - strlen($a));
    $known_modules_pattern = implode('|', array_map(
      static fn ($x) => preg_quote($x, '/'),
      $modules_by_length,
    ));
    $module_preg = '/^(?<function>(?<module>' . $known_modules_pattern . ')_(?!preprocess_)(?!update_\d)(?<hook>[a-zA-Z0-9_\x80-\xff]+$))/';
    $collector = new static($modules);
    foreach ($module_list as $module => $info) {
      $skip_procedural = in_array($module, $skipProceduralModules);
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
              $this->oopImplementations[$attribute->hook][$class . '::' . ($attribute->method ?: $method)] = $attribute->module ?? $module;
              if ($attribute->order !== NULL) {
                $this->orderOperations[$attribute->hook][0][] = $attribute->order->getOperation("$class::$method");
              }
            }
            elseif ($attribute instanceof ReOrderHook) {
              $this->orderOperations[$attribute->hook][1][] = $attribute->order->getOperation($attribute->class . '::' . $attribute->method);
            }
            elseif ($attribute instanceof RemoveHook) {
              $this->removeHookIdentifiers[$attribute->hook][] = $attribute->class . '::' . $attribute->method;
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
              assert($function === $matches['module'] . '_' . $matches['hook']);
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
    assert($function === $module . '_' . $hook);
    if ($hook === 'hook_info') {
      $this->hookInfo[] = $function;
    }
    elseif ($hook === 'module_implements_alter') {
      $message = "$function without a #[LegacyModuleImplementsAlter] attribute is deprecated in drupal:11.2.0 and removed in drupal:12.0.0. See https://www.drupal.org/node/3496788";
      @trigger_error($message, E_USER_DEPRECATED);
      $this->moduleImplementsAlters[] = $function;
    }
    $this->proceduralImplementations[$hook][] = $module;
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
   * @return array<string, array<string, array<class-string, array<string, string>>>>
   *   Hook implementation method names keyed by hook, module, class and method.
   *
   * @todo remove when ModuleHandler::add() is removed.
   *   See https://www.drupal.org/project/drupal/issues/3481778
   *
   * @internal
   */
  public function getImplementations(): array {
    $implementationsByHook = $this->getFilteredImplementations();

    // List of modules implementing hooks with the implementation details.
    $implementations = [];

    foreach ($implementationsByHook as $hook => $hookImplementations) {
      foreach ($this->modules as $module) {
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
   * @return array<string, list<\Drupal\Core\Hook\HookAttributeInterface>>
   *   Lists of Hook attribute instances by method name.
   */
  protected static function getAttributeInstances(\ReflectionClass $reflectionClass): array {
    $attributes = [];
    $reflections = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
    $reflections[] = $reflectionClass;
    foreach ($reflections as $reflection) {
      if ($reflectionAttributes = $reflection->getAttributes(HookAttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF)) {
        $method = $reflection instanceof \ReflectionMethod ? $reflection->getName() : '__invoke';
        $attributes[$method] = array_map(static fn (\ReflectionAttribute $ra) => $ra->newInstance(), $reflectionAttributes);
      }
    }
    return $attributes;
  }

}
