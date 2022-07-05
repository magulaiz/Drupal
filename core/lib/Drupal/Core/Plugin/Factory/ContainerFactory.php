<?php

namespace Drupal\Core\Plugin\Factory;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Plugin factory that creates plugin instances with dependent services.
 */
class ContainerFactory extends DefaultFactory {

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = static::getPluginClass($plugin_id, $plugin_definition, $this->interface);
    $container = \Drupal::getContainer();

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      return $plugin_class::create($container, $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly, autowiring any additional
    // constructor parameters.
    $constructor = new \ReflectionMethod($plugin_class, '__construct');
    $args = [$configuration, $plugin_id, $plugin_definition];
    foreach ($constructor->getParameters() as $pos => $parameter) {
      foreach ($parameter->getAttributes() as $attribute) {
        if ($attribute->getName() === Autowire::class) {
          $args[$pos] = $container->get((string) $attribute->newInstance()->value);
        }
      }
    }
    return new $plugin_class(...$args);
  }

}
