<?php

namespace Drupal\Core\Utility;

use Drupal\Core\DependencyInjection\ClassResolverInterface;

/**
 * Resolves PHP callables.
 *
 * The callable resolver service aims to provide a standardized approach to how
 * callables are resolved and invoked from various subsystems. The callable
 * resolver will return or invoke a callable defined in any of the following
 * definition formats:
 *
 * - Static methods: @code '\FooClass::staticMethod' @endcode
 * - Non-static methods, instantiated with the class resolver:
 *
 * @code '\DependencyInjectedClass::method' @endcode
 * - Object calls: @code [$object, 'method'] @endcode
 * - Classes with an __invoke method: @code '\ClassWithInvoke' @endcode
 * - Service notation: @code 'some.service:method' @endcode
 */
class CallableResolver {

  /**
   * Create an instance of the callable resolver.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $classResolver
   *   The class resolver.
   */
  public function __construct(
    protected ClassResolverInterface $classResolver
  ) {
  }

  /**
   * Get a callable from a definition.
   *
   * @param mixed $definition
   *   A callable definition.
   *
   * @return callable
   *   A callable.
   *
   * @throws \InvalidArgumentException
   *   Thrown when no valid callable could be resolved from the definition.
   */
  public function getCallableFromDefinition($definition): callable {
    // If the definition is natively a callable, we can return it immediately.
    if (is_callable($definition)) {
      return $definition;
    }

    if (is_array($definition)) {
      throw new \InvalidArgumentException(sprintf('The callable definition provided "[%s]" is not a valid callable.', implode(",", $definition)));
    }

    if (!is_string($definition)) {
      throw new \InvalidArgumentException(sprintf('The callable definition provided was invalid. Illegal format of type: %s', gettype($definition)));
    }

    // Callable with __invoke().
    if (!str_contains($definition, ':')) {
      $instance = $this->classResolver->getInstanceFromDefinition($definition);
      if (!is_callable($instance)) {
        throw new \InvalidArgumentException(sprintf('The callable definition provided was invalid. Class "%s" does not have a method "__invoke" and is not callable.', $instance::class));
      }
      return $instance;
    }

    // Callable in the service:method notation.
    $classOrService = NULL;
    $method = NULL;
    $count = substr_count($definition, ':');
    if ($count == 1) {
      [$classOrService, $method] = explode(':', $definition, 2);
    }
    // Callable in the class::method notation.
    if (str_contains($definition, '::')) {
      [$classOrService, $method] = explode('::', $definition, 2);
    }
    if (empty($classOrService) || empty($method)) {
      throw new \InvalidArgumentException(sprintf('The callable definition provided was invalid. Could not get class and method from definition "%s".', $definition));
    }

    $instance = $this->classResolver->getInstanceFromDefinition($classOrService);
    if (!is_callable([$instance, $method])) {
      throw new \InvalidArgumentException(sprintf('The callable definition provided was invalid. Either class "%s" does not have a method "%s", or it is not callable.', $instance::class, $method));
    }
    return [$instance, $method];
  }

}
