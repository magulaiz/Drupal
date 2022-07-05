<?php

namespace Drupal\Core\Plugin\Factory;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Plugin\Attribute\Service;
use Drupal\Core\Plugin\ContainerAutowiredPluginInterface;

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

    // If the plugin provides service attributes, construct it from those.
    if (is_subclass_of($plugin_class, ContainerAutowiredPluginInterface::class)) {
      $constructor = new \ReflectionMethod($plugin_class, '__construct');
      $args = [$configuration, $plugin_id, $plugin_definition];
      foreach ($constructor->getParameters() as $pos => $parameter) {
        foreach ($parameter->getAttributes() as $attribute) {
          if ($attribute->getName() === Service::class) {
            $args[$pos] = \Drupal::service($attribute->newInstance()->service);
          }
        }
      }
      return new $plugin_class(...$args);
    }

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly.
    return new $plugin_class($configuration, $plugin_id, $plugin_definition);
  }

}
