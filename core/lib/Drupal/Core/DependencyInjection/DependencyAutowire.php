<?php

declare(strict_types=1);

namespace Drupal\Core\DependencyInjection;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides a service for auto-wire dependencies.
 */
final class DependencyAutowire {

  /**
   * Constructs a new DependencyAutowire object.
   */
  public function __construct(
    protected ContainerInterface $container,
  ) {}

  /**
   * Creates new instance by auto-wiring dependencies.
   *
   * @param \ReflectionClass $class
   *   The reflection class.
   * @param array $prefilled_args
   *   The array of pre-filled args where the key is the name of the argument.
   *
   * @return object
   *   The instance of the class.
   *
   * @throws \ReflectionException
   */
  public function autowireClass(\ReflectionClass $class, array $prefilled_args = []): object {
    $construct = $class->getConstructor();

    if (!$construct || !$construct->isPublic()) {
      return $class->newInstanceWithoutConstructor();
    }

    $args = $this->autowireMethod($construct, $prefilled_args);

    return $class->newInstanceArgs($args);
  }

  /**
   * Creates a list of method arguments by auto-wiring dependencies.
   *
   * @param \ReflectionMethod $method
   *   The reflection method.
   * @param array $prefilled_args
   *   The array of pre-filled args where the key is the name of the argument.
   *
   * @return array
   *   A list of method arguments.
   */
  public function autowireMethod(\ReflectionMethod $method, array $prefilled_args = []): array {
    $args = [];

    foreach ($method->getParameters() as $parameter) {
      $parameter_name = $parameter->getName();

      if (isset($prefilled_args[$parameter_name])) {
        $args[] = $prefilled_args[$parameter_name];
        continue;
      }

      foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
        $value = $attribute->newInstance()->value;

        if (is_string($value)) {
          $args[] = $this->container->getParameter($value);
          continue 2;
        }

        if ($value instanceof Reference) {
          $args[] = $this->container->get((string) $value);
          continue 2;
        }
      }

      $type = $parameter->getType();
      $service = $type ? $this->getServiceReferenceFromType($type) : NULL;

      if (!$service && $parameter->isDefaultValueAvailable()) {
        $args[] = $parameter->getDefaultValue();
        continue;
      }

      if (!$service && $parameter->allowsNull()) {
        $args[] = NULL;
        continue;
      }

      if (!$service) {
        $class = $method->getDeclaringClass()->getName();
        $method_name = $method->getName();
        $service = $type ? $type->getName() : '';

        throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::%s()", you should configure its value explicitly.', $service, $parameter_name, $class, $method_name));
      }

      $args[] = $this->container->get((string) $service);
    }

    return $args;
  }

  /**
   * Gets service reference reflection from type.
   *
   * @param \ReflectionType $type
   *   The reflection type.
   *
   * @return \Symfony\Component\DependencyInjection\Reference|null
   *   Service reference or NULL if the service cannot be retrieved.
   */
  public function getServiceReferenceFromType(\ReflectionType $type): ?Reference {
    if ($type instanceof \ReflectionNamedType && $this->container->has($type->getName())) {
      return new Reference($type->getName());
    }

    if ($type instanceof \ReflectionUnionType) {
      foreach ($type->getTypes() as $subtype) {
        if ($service = $this->getServiceReferenceFromType($subtype)) {
          return $service;
        }
      }
    }

    return NULL;
  }

}
