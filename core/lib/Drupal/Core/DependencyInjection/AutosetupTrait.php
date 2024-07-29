<?php

declare(strict_types=1);

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Defines a trait for automatically wiring dependencies from the container.
 */
trait AutosetupTrait {

  /**
   * Setup the test class using autowiring.
   */
  protected function setUp(): void {
    parent::setUp();
    $class = new \ReflectionClass($this);
    foreach ($class->getProperties() as $property) {
      if ($property->isInitialized($this) || !$property->hasType()) {
        continue;
      }
      $service = ltrim((string) $property->getType(), '?');
      foreach ($property->getAttributes(Autowire::class) as $attribute) {
        $service = (string) $attribute->newInstance()->value;
      }

      if (!$this->container->has($service)) {
        throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": property "$%s" of class "%s", you should configure its value explicitly.', $service, $property->getName(), static::class));
      }

      $property->setValue($this, $this->container->get($service));
    }
  }

}
