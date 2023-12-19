<?php

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Implements the class resolver interface supporting class names and services.
 */
class ClassResolver implements ClassResolverInterface, ContainerAwareInterface {
  use DependencySerializationTrait;
  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public function getInstanceFromDefinition($definition) {
    if ($this->container->has($definition)) {
      $instance = $this->container->get($definition);
    }
    else {
      if (!class_exists($definition)) {
        throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $definition));
      }

      if (is_subclass_of($definition, 'Drupal\Core\DependencyInjection\ContainerInjectionInterface')) {
        $instance = $definition::create($this->container);
      }
      elseif (method_exists($definition, '__construct')) {
        $constructor = new \ReflectionMethod($definition, '__construct');
        $args = [];

        foreach ($constructor->getParameters() as $parameter) {
          $service = (string) $parameter->getType();

          foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
            $service = (string) $attribute->newInstance()->value;
          }

          if (!$this->container->has($service)) {
            throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), static::class));
          }

          $args[] = $this->container->get($service);
        }

        $instance = new $definition(...$args);
      }
      else {
        $instance = new $definition();
      }
    }

    if ($instance instanceof ContainerAwareInterface) {
      $instance->setContainer($this->container);
    }

    return $instance;
  }

}
