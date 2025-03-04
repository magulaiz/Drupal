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
use Symfony\Component\DependencyInjection\Definition;

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
   * A list of attributes for hook implementations.
   *
   * Keys are module, class and method. Values are Hook attributes.
   *
   * @var array<string, array<class-string, array<string, list<\Drupal\Core\Hook\HookOperation>>>>
   */
  protected array $moduleHooks = [];

  /**
   * {@inheritdoc}
   *
   * @return array<string, array<string, array<class-string, array<string, string>>>>
   *   Hook implementation method names keyed by hook, module, class and method.
   */
  public function process(ContainerBuilder $container): array {
    $collector = static::collectAllHookImplementations($container->getParameter('container.modules'), $container);

    // List of modules implementing hooks with the implementation details.
    $implementations = [];

    // Hooks that should be ordered together when extra types are involved.
    $orderExtraTypes = [];

    // Hook attributes that contain ordering information.
    $hookOrderOperations = [];

    // List of modules that the hooks are defined for, keyed by class and
    // method.
    $moduleFinder = [];

    // These attributes need to be processed after all hooks have been
    // processed.
    $processAfter = [
      RemoveHook::class => [],
      ReOrderHook::class => [],
    ];
    foreach (array_keys($container->getParameter('container.modules')) as $module) {
      foreach ($collector->moduleHooks[$module] ?? [] as $class => $attributesByMethod) {
        foreach ($attributesByMethod as $method => $attributes) {
          foreach ($attributes as $hookAttribute) {
            assert($hookAttribute instanceof HookOperation);
            if (isset($processAfter[get_class($hookAttribute)])) {
              $processAfter[get_class($hookAttribute)][] = $hookAttribute;
              continue;
            }
            if (!$hookAttribute instanceof Hook) {
              // This is an unsupported attribute class, the code below would
              // not work.
              continue;
            }
            if ($class !== ProceduralCall::class) {
              self::checkForProceduralOnlyHooks($hookAttribute, $class);
            }
            // Set properties on hook class that are needed for registration.
            $hookAttribute->set($class, $module, $method);
            // Store the implementation details for registering the hook.
            $implementations[$hookAttribute->hook][$hookAttribute->module][$class][$hookAttribute->method] = $hookAttribute->method;
            // Reverse lookup for modules implementing hooks.
            $moduleFinder[$class][$hookAttribute->method][$hookAttribute->hook] = $hookAttribute->module;
            if ($hookAttribute->order) {
              $hookOrderOperations[] = $hookAttribute;
            }
          }
        }
      }
    }

    // Loop over all RemoveHook attributes and remove them from the maps before
    // registering the hooks. This must happen after all collection, but before
    // registration to ensure the hook it is removing has already been
    // discovered.
    foreach ($processAfter[RemoveHook::class] as $removeHook) {
      if ($module = ($moduleFinder[$removeHook->class][$removeHook->method][$removeHook->hook] ?? '')) {
        // Remove the hook implementation for the defined class, method, and
        // hook.
        unset($implementations[$removeHook->hook][$module][$removeHook->class][$removeHook->method]);
        // Remove empty arrays, after the entry was removed.
        // Hook implementation removal is expected to be rare, therefore it will
        // be faster to do it like this than cleaning the entire tree
        // afterwards.
        if (empty($implementations[$removeHook->hook][$module][$removeHook->class])) {
          unset($implementations[$removeHook->hook][$module][$removeHook->class]);
          if (empty($implementations[$removeHook->hook][$module])) {
            unset($implementations[$removeHook->hook][$module]);
            if (empty($implementations[$removeHook->hook])) {
              unset($implementations[$removeHook->hook]);
            }
          }
        }
      }
    }

    // Loop over all ReOrderHook attributes and gather order information
    // before registering the hooks. This must happen after all collection,
    // but before registration to ensure this ordering directive takes
    // precedence.
    foreach ($processAfter[ReOrderHook::class] as $reOrderHook) {
      $hookOrderOperations[] = $reOrderHook;
    }

    foreach ($hookOrderOperations as $hookWithOrder) {
      if ($hookWithOrder->order instanceof ComplexOrder && $hookWithOrder->order->extraTypes) {
        $extraTypes = [... $hookWithOrder->order->extraTypes, $hookWithOrder->hook];
        foreach ($extraTypes as $extraHook) {
          $orderExtraTypes[$extraHook] = array_merge($orderExtraTypes[$extraHook] ?? [], $extraTypes);
        }
      }
    }
    $orderExtraTypes = array_map('array_unique', $orderExtraTypes);

    // @todo investigate whether this if() is needed after ModuleHandler::add()
    // is removed.
    // @see https://www.drupal.org/project/drupal/issues/3481778
    if (count($container->getDefinitions()) > 1) {
      static::registerImplementations($container, $collector, $implementations, $orderExtraTypes, $hookOrderOperations, $moduleFinder);
    }
    return $implementations;
  }

  /**
   * Register hook implementations as event listeners.
   *
   * Passes required include and ordering information to module_handler.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container.
   * @param \Drupal\Core\Hook\HookCollectorPass $collector
   *   The collector.
   * @param array<string, array<string, array<class-string, list<string>>>> $implementations
   *   All implementations, as method names keyed by hook, module and class.
   * @param array<string, list<string>> $orderExtraTypes
   *   Extra types to order a hook with.
   * @param list<\Drupal\Core\Hook\HookOperation> $hookOrderOperations
   *   All attributes that contain ordering information.
   * @param array<class-string, array<string, array<string, string>>> $moduleFinder
   *   Lookup map to find the module for each hook implementation.
   *   Array keys are the class, method, and hook, array values are module
   *   names.
   *   The module name can be different from the module the class is in,
   *   because an implementation can be on behalf of another module.
   */
  protected static function registerImplementations(
    ContainerBuilder $container,
    HookCollectorPass $collector,
    array $implementations,
    array $orderExtraTypes,
    array $hookOrderOperations,
    array $moduleFinder,
  ): void {
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

    // List of hooks and modules formatted for hook_module_implements_alter().
    $moduleImplementsMap = [];
    foreach ($implementations as $hook => $implementationsByModule) {
      foreach ($implementationsByModule as $module => $implementationsByClass) {
        $moduleImplementsMap[$hook][$module] = '';
      }
    }

    $tagsInfoByClass = [];
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
      // Start at 0 for the first hook. We decrease the priority after each
      // hook that is registered. Symfony priorities run higher priorities
      // first.
      $priority = 0;
      foreach ($moduleImplements as $module => $v) {
        foreach ($implementations[$hook][$module] ?? [] as $class => $methods) {
          foreach ($methods as $method) {
            $tagsInfoByClass[$class][] = [
              'event' => "drupal_hook.$hook",
              'method' => $method,
              'priority' => $priority,
            ];
            --$priority;
            $map[$hook][$class][$method] = $module;
          }
        }
        unset($implementations[$hook][$module]);
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

    // Pass necessary parameters to moduleHandler.
    $definition = $container->getDefinition('module_handler');
    $definition->setArgument('$groupIncludes', $groupIncludes);
    $definition->setArgument('$orderedExtraTypes', $orderExtraTypes);
    $container->setParameter('hook_implementations_map', $map ?? []);

    $hookPriority = new HookPriority($container);
    foreach ($hookOrderOperations as $hookOrderOperation) {
      assert($hookOrderOperation instanceof HookOperation);
      // ::process() adds the hook serving as key to the order extraTypes so it
      // does not need to be added if there's a extraTypes for the hook.
      $hooks = $orderExtraTypes[$hookOrderOperation->hook] ?? [$hookOrderOperation->hook];
      $combinedHook = implode(':', $hooks);
      if ($hookOrderOperation->order instanceof ComplexOrder) {
        // Verify the correct structure of
        // $hookOrderOperation->order->classesAndMethods and create specifiers
        // for HookPriority::change() while at it.
        $otherSpecifiers = array_map(
          static function ($pair) {
            if (!is_array($pair)) {
              return throw new \LogicException('classesAndMethods needs to be an array of arrays');
            }
            return $pair[0] . '::' . $pair[1];
          },
          $hookOrderOperation->order->classesAndMethods
        );
        // Collect classes and methods for
        // self::registerComplexHookImplementations().
        $classesAndMethods = $hookOrderOperation->order->classesAndMethods;
        foreach ($hookOrderOperation->order->modules as $module) {
          foreach ($hooks as $hook) {
            foreach ($implementations[$hook][$module] ?? [] as $class => $methods) {
              foreach ($methods as $method) {
                $classesAndMethods[] = [$class, $method];
                $otherSpecifiers[] = "$class::$method";
              }
            }
          }
        }
        if (count($hooks) > 1) {
          // The hook implementation in $hookOrderOperation and everything in
          // $classesAndMethods will be ordered relative to each other as if
          // they were implementing a single hook. This needs to be marked on
          // their service definition and added to the
          // hook_implementations_map container parameter.
          $classesAndMethods[] = [$hookOrderOperation->class, $hookOrderOperation->method];
          self::registerComplexHookImplementations($container, $classesAndMethods, $moduleFinder, $combinedHook);
        }
      }
      else {
        $otherSpecifiers = NULL;
      }
      $hookPriority->change("drupal_hook.$combinedHook", $hookOrderOperation, $otherSpecifiers);
    }
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
      $collector->collectModuleHookImplementations(dirname($info['pathname']), $module, $module_preg, $skip_procedural, $container);
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
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder|null $container
   *   The container.
   */
  protected function collectModuleHookImplementations($dir, $module, $module_preg, bool $skip_procedural, ?ContainerBuilder $container = NULL): void {
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
            $reflectionClass = $container?->getReflectionClass($class) ?? new \ReflectionClass($class);
            $attributes = self::getAttributeInstances($reflectionClass);
            $hook_file_cache->set($filename, ['class' => $class, 'attributes' => $attributes]);
          }
        }
        $this->moduleHooks[$module][$class] = $attributes;
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
    $this->moduleHooks[$module][ProceduralCall::class][$function] = [new Hook($hook, method: $module . '_' . $hook)];
    if ($hook === 'hook_info') {
      $this->hookInfo[] = $function;
    }
    if ($hook === 'module_implements_alter') {
      $message = "$function without a #[LegacyModuleImplementsAlter] attribute is deprecated in drupal:11.2.0 and removed in drupal:12.0.0. See https://www.drupal.org/node/3496788";
      @trigger_error($message, E_USER_DEPRECATED);
      $this->moduleImplementsAlters[] = $function;
    }
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
    $container = new ContainerBuilder();
    $container->setParameter('container.modules', $paths);
    return $this->process($container);
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

  /**
   * Adds an event listener tag to a service definition.
   *
   * @param \Symfony\Component\DependencyInjection\Definition $definition
   *   The service definition.
   * @param string|int $hook
   *   The name of the hook.
   * @param string $method
   *   The method.
   * @param int $priority
   *   The priority.
   *
   * @return int
   *   A new priority, guaranteed to be lower than $priority.
   */
  protected static function addTagToDefinition(Definition $definition, string|int $hook, string $method, int $priority): int {
    $definition->addTag('kernel.event_listener', [
      'event' => "drupal_hook.$hook",
      'method' => $method,
      'priority' => $priority--,
    ]);
    return $priority;
  }

  /**
   * Register complex hook implementations.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container.
   * @param list<array{class-string, string}> $classesAndMethods
   *   A list of class-and-method pairs.
   * @param array<class-string, array<string, array<string, string>>> $moduleFinder
   *   Array keys are the class, method, and hook, array values are module
   *   names.
   * @param string $combinedHook
   *   A string made form list of hooks separated by :.
   */
  protected static function registerComplexHookImplementations(ContainerBuilder $container, array $classesAndMethods, array $moduleFinder, string $combinedHook): void {
    $map = $container->getParameter('hook_implementations_map');
    $priority = 0;
    foreach ($classesAndMethods as [$class, $method]) {
      // Ordering against not installed modules is possible.
      if (isset($moduleFinder[$class][$method])) {
        if (count(array_unique($moduleFinder[$class][$method])) > 1) {
          throw new \LogicException('Complex ordering can only work when all implementations on a single method are for the same module.');
        }
        $map[$combinedHook][$class][$method] = reset($moduleFinder[$class][$method]);
        $priority = self::addTagToDefinition($container->findDefinition($class), $combinedHook, $method, $priority);
      }
    }
    $container->setParameter('hook_implementations_map', $map);
  }

}
