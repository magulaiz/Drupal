<?php

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Defines a trait for automatically wiring dependencies from the container.
 *
 * This trait uses reflection and may cause performance issues with classes
 * that will be instantiated multiple times.
 */
trait AutowireTrait {

  /**
   * Instantiates a new instance of the implementing class using autowiring.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    $args = [];

    if (method_exists(static::class, '__construct')) {
      $constructor = new \ReflectionMethod(static::class, '__construct');
      foreach ($constructor->getParameters() as $parameter) {
        $type = $parameter->getType();
        $service = NULL;

        foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
          $service = (string) $attribute->newInstance()->value;
        }

        if (!$service && $type instanceof \ReflectionUnionType) {
          foreach ($type->getTypes() as $subtype) {
            if (!$subtype->isBuiltin() || $container->has($subtype->getName())) {
              $service = $subtype->getName();
              break;
            }
          }
        }

        if (!$service) {
          $service = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;
        }

        $service_exists = $container->has($service);

        if (!$service_exists && $parameter->isDefaultValueAvailable()) {
          $args[] = $parameter->getDefaultValue();
          continue;
        }

        if (!$service_exists && $parameter->allowsNull()) {
          $args[] = NULL;
          continue;
        }

        if (!$service_exists) {
          throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), static::class));
        }

        $args[] = $container->get($service);
      }
    }

    return new static(...$args);
  }

}
