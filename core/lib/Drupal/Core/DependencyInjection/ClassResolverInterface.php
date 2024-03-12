<?php

namespace Drupal\Core\DependencyInjection;

/**
 * Provides interface to get an instance of a class with dependency injection.
 */
interface ClassResolverInterface {

  /**
   * Returns a class instance with a given class definition.
   *
   * In contrast to controllers you don't specify a method.
   *
   * @param string $definition
   *   A class name or service name.
   *
   * @return object
   *   The instance of the class.
   *
   * @throws \InvalidArgumentException
   *   If $class is not a valid service identifier and the class does not exist.
   * @throws \Symfony\Component\DependencyInjection\Exception\AutowiringFailedException
   *   Thrown when a definition cannot be autowired.
   */
  public function getInstanceFromDefinition($definition);

}
