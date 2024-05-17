<?php

namespace Drupal\Core\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers hooks and tags them as event listeners.
 */
class HookHelper {

  const HOOK_IMPLEMENTATIONS = 'hook_implementations';


  /**
   * Register OOP hook implementations.
   *
   * Register classes marked with the Hook attribute in the
   * Drupal\modulename\Hooks namespace as autowired services and gather Hook
   * implementations into the hook_implementations container parameter.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container.
   * @param array $module_filenames
   *   An associative array. Keys are the module names, values are relevant
   *   info yml file path.
   * @param array $module_weights
   *   An associative array. Keys are the module names, values are weights.
   *
   */
  public static function registerHooks(ContainerBuilder $container, array $module_filenames, array $module_weights): void {
    $implementations = [];
    foreach ($module_filenames as $module => $info_file) {
      $dir = dirname($info_file) . '/src/Hook';
      if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $filename) {
          $class = implode('\\', ['Drupal', $module, 'Hook', basename($filename, '.php')]);
          if ($class_implementations = static::getHookImplementationsInClass($class)) {
            if (!$container->has($class)) {
              $container->register($class, $class)->setAutowired(TRUE);
            }
            foreach ($class_implementations as $attribute) {
              assert($attribute instanceof Hook);
              $implementations[$attribute->hook][$class][$attribute->method] = [
                'priority' => $attribute->priority ?? -$module_weights[$module],
                'module' => $attribute->module ?? $module,
              ];
            }
          }
        }
      }
    }
    $container->setParameter(self::HOOK_IMPLEMENTATIONS, $implementations);
  }

  /**
   * Get Hook attributes from a class.
   *
   * @param string $class
   *   The class.
   *
   * @return \Drupal\Core\Extension\Hook[]
   *   An array of Hook attributes on this class with method set.
   */
  protected static function getHookImplementationsInClass(string $class): array {
    if (!class_exists($class)) {
      return [];
    }
    $reflection_class = new \ReflectionClass($class);
    $class_implementations = [];
    foreach ($reflection_class->getAttributes(Hook::class) as $reflection_attribute) {
      $hook = $reflection_attribute->newInstance();
      if (!$hook->method) {
        throw new \LogicException("The Hook attribute for hook $hook->hook on class $class must specify a method.");
      }
      $class_implementations[] = $hook;
    }
    foreach ($reflection_class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method_reflection) {
      foreach ($method_reflection->getAttributes(Hook::class) as $attribute_reflection) {
        $class_implementations[] = $attribute_reflection->newInstance()->setMethod($method_reflection->getName());
      }
    }
    return $class_implementations;
  }

  public static function getPrioritiesForHook(array $implementations, $hook): array {
    $priorities = [];
    foreach ($implementations[$hook] as $class_implementation) {
      foreach ($class_implementation as $implementation) {
        $priorities[] = $implementation['priority'];
      }
    }
    return $priorities;
  }

}
