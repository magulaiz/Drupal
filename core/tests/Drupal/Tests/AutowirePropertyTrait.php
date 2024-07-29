<?php

declare(strict_types=1);

namespace Drupal\Tests;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Autowire properties.
 */
trait AutowirePropertyTrait {

  /**
   * Autowire properties.
   */
  public function autowireProperties(): void {
    $class = new \ReflectionClass($this);
    if (!\property_exists($this, 'container') || !$this->container instanceof ContainerInterface) {
      throw new \RuntimeException(sprintf('Cannot autowire properties of class "%s" as it does not have a container.', static::class));
    }

    foreach ($class->getProperties() as $property) {
      // Only autowire properties that have not been initialized yet and have
      // the AutowireProperty attribute.
      if ($property->isInitialized($this) || !$property->getAttributes(AutowireProperty::class)) {
        continue;
      }
      // If the property has no type, we cannot autowire it.
      if (!$property->hasType()) {
        throw new \RuntimeException(sprintf('Cannot autowire property "%s" of class "%s" as it has no type.', $property->getName(), static::class));
      }
      // Find a matching service in the container.
      $service = ltrim((string) $property->getType(), '?');
      if (!$this->container->has($service)) {
        throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": property "$%s" of class "%s", you should configure its value explicitly.', $service, $property->getName(), static::class));
      }
      // Set the property to the service from the container.
      $property->setValue($this, $this->container->get($service));
    }
  }

}
