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

    // Check if the constructor can be autowired by traversing the hierarchy.
    $constructor_class = $plugin_class;
    $args = [$configuration, $plugin_id, $plugin_definition];
    do {
      $constructor = new \ReflectionMethod($constructor_class, '__construct');
      $parameters = $constructor->getParameters();

      // Store the original number of parameters to check later.
      if (!isset($parameter_count)) {
        $parameter_count = count($parameters);
      }

      // Check each argument that has not yet been filled in.
      foreach ($parameters as $pos => $parameter) {
        if (!isset($args[$pos])) {
          foreach ($parameter->getAttributes() as $attribute) {
            if ($attribute->getName() === Autowire::class) {
              $args[$pos] = \Drupal::service((string) $attribute->newInstance()->value);
            }
          }
        }
      }
      $constructor_class = get_parent_class($constructor_class);
    } while ($constructor_class && count($args) !== $parameter_count);

    // If we couldn't autowire the plugin and it provides a factory method,
    // pass the container to it.
    if (count($args) !== $parameter_count && method_exists($plugin_class, 'create')) {
      return $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition);
    }

    // Otherwise, create the plugin directly.
    ksort($args);
    return new $plugin_class(...$args);
  }

}
