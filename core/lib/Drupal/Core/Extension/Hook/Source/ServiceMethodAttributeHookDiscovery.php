<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\Source;

use Drupal\Core\Attribute\Hook\HookAttributeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Discovery for service method hook implementations.
 */
class ServiceMethodAttributeHookDiscovery implements ImplementationSourceInterface {

  /**
   * Constructor.
   *
   * @param array<string, array<string, string>> $serviceClassesByModule
   *   Service classes by module name and service id.
   */
  public function __construct(
    #[Autowire('%hook_services%')]
    private readonly array $serviceClassesByModule,
  ) {
    assert($this->validate());
  }

  /**
   * Validates object integrity.
   *
   * This is only called with assertions enabled, to assist during development.
   *
   * @return bool
   *   TRUE, to be returned to assert().
   */
  private function validate(): bool {
    foreach ($this->serviceClassesByModule as $module => $service_classes) {
      assert(is_string($module));
      foreach ($service_classes as $service_id => $class) {
        assert(is_string($service_id));
        assert(is_string($class));
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getImplementations(): array {
    $implementations = [];
    foreach ($this->serviceClassesByModule as $module => $service_classes) {
      foreach ($service_classes as $service_id => $class) {
        try {
          $reflectionClass = new \ReflectionClass($class);
        }
        catch (\ReflectionException $e) {
          throw new \RuntimeException("Class $class not found. Module: $module, service: $service_id.");
        }
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $reflectionMethod) {
          if ($reflectionMethod->isStatic()) {
            continue;
          }
          $attributesHooks = $reflectionMethod->getAttributes(HookAttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
          $default_info = [
            'module' => $module,
            'service' => $service_id,
            'method' => $reflectionMethod->name,
          ];
          foreach ($attributesHooks as $attribute) {
            /** @var \Drupal\Core\Attribute\Hook\HookAttributeInterface $instance */
            $instance = $attribute->newInstance();
            $hooks = $instance->getHookNames();
            $specific_info = $instance->getInfo() + $default_info;
            foreach ($hooks as $hook) {
              $implementations[$hook][] = $specific_info;
            }
          }
        }
      }
    }
    return $implementations;
  }

}
