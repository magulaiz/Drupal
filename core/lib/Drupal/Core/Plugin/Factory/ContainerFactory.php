<?php

namespace Drupal\Core\Plugin\Factory;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Plugin factory which passes a container to a create method.
 */
class ContainerFactory extends DefaultFactory {

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $plugin_definition = $this->discovery->getDefinition($plugin_id);
    $plugin_class = static::getPluginClass($plugin_id, $plugin_definition, $this->interface);

    // If we know how to autowire the plugin, construct it automatically.
    if (isset($plugin_definition['constructor_services'])) {
      $container = \Drupal::getContainer();
      $args = array_map(fn(Reference $service): object => $container->get((string) $service), $plugin_definition['constructor_services']);
      if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
        @trigger_error(sprintf('Implementing ContainerFactoryPluginInterface in the plugin "%s" is deprecated in drupal:11.2.0 and will be removed in drupal:12.0.0. The create() method can be safely removed. See https://www.drupal.org/node/7654321', $plugin_id), E_USER_DEPRECATED);
      }
      return new $plugin_class(...[$configuration, $plugin_id, $plugin_definition, ...$args]);
    }

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly.
    return new $plugin_class($configuration, $plugin_id, $plugin_definition);
  }

}
