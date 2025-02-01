<?php

namespace Drupal\Core\Plugin\Factory;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
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
    if ($plugin_definition instanceof PluginDefinitionInterface) {
      $services = $plugin_definition->constructorServices ?? NULL;
    }
    else {
      $services = $plugin_definition['constructor_services'] ?? NULL;
    }
    if (!is_null($services)) {
      $container = \Drupal::getContainer();
      $args = array_map(fn(Reference $service): object => $container->get((string) $service), $services);
      try {
        $plugin = new $plugin_class(...[$configuration, $plugin_id, $plugin_definition, ...$args]);
        if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
          @trigger_error(sprintf('Implementing ContainerFactoryPluginInterface in the \\%s plugin "%s" is deprecated in drupal:11.2.0 and will be removed in drupal:12.0.0. The create() method can be safely removed. See https://www.drupal.org/node/7654321', $plugin_class, $plugin_id), E_USER_DEPRECATED);
        }
        return $plugin;
      }
      catch (\ArgumentCountError) {
        // If the plugin class does not have a constructor that matches the
        // services, fall back to the previous behavior, but issue a warning.
        // @todo Trigger autowiring another way for this case?
        @trigger_error(sprintf('Altering autowired plugins in the \\%s plugin "%s" is deprecated in drupal:11.2.0 and will be removed in drupal:12.0.0. Update the constructor services list. See https://www.drupal.org/node/7654321', $plugin_class, $plugin_id), E_USER_DEPRECATED);
      }
    }

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly.
    return new $plugin_class($configuration, $plugin_id, $plugin_definition);
  }

}
