<?php

namespace Drupal\Core\TypedData\Options;

use Drupal\Core\DependencyInjection\ClassResolverInterface;

/**
 * Resolves options provider definitions.
 */
class OptionsProviderResolver {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver to use.
   */
  public function __construct(ClassResolverInterface $class_resolver) {
    $this->classResolver = $class_resolver;
  }

  /**
   * Returns an options provider for the given definition and arguments.
   *
   * @param string|null $definition
   *   The options provider definition; e.g., a class name, or NULL if
   *   undefined.
   *
   * @return \Drupal\Core\TypedData\OptionsProviderInterface|null
   *   The options provider, or NULL if none is defined.
   *
   * @see \Drupal\Core\TypedData\TypedDataManager::getOptionsProvider()
   */
  public function getOptionsProvider($definition) {
    if (!isset($definition)) {
      return;
    }
    // If the definition is a callable, wrap it in a suiting options provider.
    if ($callable = $this->getCallableFromDefinition($definition)) {
      $provider = new CallableOptionsProvider($callable);
    }
    else {
      $provider = $this->classResolver->getInstanceFromDefinition($definition);
    }
    return $provider;
  }

  /**
   * Returns a callable for the given definition.
   *
   * @param string $definition
   *   The options provider definition.
   *
   * @return mixed|null
   *   The callable, or NULL if no callable was defined.
   */
  protected function getCallableFromDefinition($definition) {
    if (strpos($definition, ':') === FALSE && function_exists($definition)) {
      return $definition;
    }
    // Definition is in the service:method notation.
    elseif (substr_count($definition, ':') == 1) {
      list($service, $method) = explode(':', $definition, 2);
      $instance = $this->classResolver->getInstanceFromDefinition($service);
      return [$instance, $method];
    }
    // Definition is in the class::method notation for static methods.
    elseif (strpos($definition, '::') !== FALSE) {
      return explode('::', $definition, 2);
    }
  }

}
