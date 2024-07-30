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

    foreach ($class->getProperties() as $property) {
      // Only autowire properties that have not been initialized yet and have
      // the AutowireProperty attribute.
      $attr = $property->getAttributes(AutowireProperty::class);
      if ($property->isInitialized($this) || !$attr) {
        continue;
      }
      if (!\property_exists($this, 'container') || !$this->container instanceof ContainerInterface) {
        throw new \RuntimeException(sprintf('Cannot autowire properties of class "%s" as there is no container available.', static::class));
      }
      if (count($attr[0]->getArguments()) !== 0) {
        $service = $attr[0]->getArguments()['service'] ?? '';
      }
      else {
        // When no arguments in the attribute, try finding out from the property
        // type.
        if (!$property->hasType()) {
          continue;
        }
        $service = ltrim((string) $property->getType(), '?');
      }
      if (!$this->container->has($service)) {
        throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": property "$%s" of class "%s".', $service, $property->getName(), static::class));
      }
      // Set the property to the service from the container.
      $property->setValue($this, $this->container->get($service));
    }
  }

}
