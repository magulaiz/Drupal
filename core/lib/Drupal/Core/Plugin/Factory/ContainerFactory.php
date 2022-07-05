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

    // Check if the constructor can be autowired by traversing the hierarchy.
    $constructor_class = $plugin_class;
    $args = [$configuration, $plugin_id, $plugin_definition];
    do {
      $constructor = new \ReflectionMethod($constructor_class, '__construct');
      if (!isset($parameters)) {
        $parameters = $constructor->getParameters();
      }
      foreach ($constructor->getParameters() as $pos => $parameter) {
        foreach ($parameter->getAttributes() as $attribute) {
          if ($attribute->getName() === Autowire::class) {
            $args[$pos] = $container->get((string) $attribute->newInstance()->value);
          }
        }
      }
      $constructor_class = get_parent_class($constructor_class);
    } while ($constructor_class && count($args) !== count($parameters));

    // If we couldn't autowire the plugin and it provides a factory method,
    // pass the container to it.
    if (count($args) !== count($parameters) && method_exists($plugin_class, 'create')) {
      return $plugin_class::create($container, $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly.
    ksort($args);
    return new $plugin_class(...$args);
  }

}
