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
    if (static::supportsAutowiring($plugin_definition)) {
      if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
        @trigger_error(sprintf('Implementing %s in %s while enabling autowiring is deprecated in drupal:11.2.0 and will be removed in drupal:12.0.0. Remove the interface and create() method. See https://www.drupal.org/node/7654321', ContainerFactoryPluginInterface::class, $plugin_class), E_USER_DEPRECATED);
      }
      $args = static::getPluginArguments($plugin_definition);
      return new $plugin_class(...[$configuration, $plugin_id, $plugin_definition, ...$args]);
    }

    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, ContainerFactoryPluginInterface::class)) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }

    // Backward compatibility for plugins that provide a factory method but
    // the parent no longer implements the interface (because it is autowired).
    if (method_exists($plugin_class, 'create')) {
      $method = new \ReflectionMethod($plugin_class, 'create');
      // Additional checks are required to be sure we have a factory method.
      // @see \Drupal\Tests\Core\Menu\MenuLinkMock::create()
      if ($method->isStatic() && count($method->getParameters()) === 4) {
        @trigger_error(sprintf('Implementing a factory method in %s without implementing %s is deprecated in drupal:11.2.0 and will be removed in drupal:12.0.0. Implement the interface or convert the plugin to use autowiring. See https://www.drupal.org/node/7654321', $plugin_class, ContainerFactoryPluginInterface::class), E_USER_DEPRECATED);
        return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
      }
    }

    // Otherwise, create the plugin directly.
    return new $plugin_class($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Determine whether a plugin supports autowiring.
   *
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $plugin_definition
   *   The plugin definition.
   *
   * @return bool
   *   TRUE if the plugin supports autowiring, FALSE otherwise.
   */
  public static function supportsAutowiring(array|PluginDefinitionInterface $plugin_definition): bool {
    if (is_array($plugin_definition)) {
      return $plugin_definition['autowire'] ?? FALSE;
    }
    else {
      return $plugin_definition->autowire ?? FALSE;
    }
  }

  /**
   * Get the service arguments to pass to the plugin constructor.
   *
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $plugin_definition
   *   The plugin definition.
   *
   * @return array
   *   The set of services to pass to the constructor.
   */
  public static function getPluginArguments(array|PluginDefinitionInterface $plugin_definition): array {
    if (is_array($plugin_definition)) {
      $services = $plugin_definition['constructor_services'] ?? [];
    }
    else {
      $services = $plugin_definition->constructorServices ?? [];
    }

    $container = \Drupal::getContainer();
    return array_map(fn(Reference $service): object => $container->get((string) $service), $services);
  }

}
