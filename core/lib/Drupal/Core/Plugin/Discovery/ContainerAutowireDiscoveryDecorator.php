<?php

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Allows a plugin discovery to be autowire aware.
 */
class ContainerAutowireDiscoveryDecorator implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * Creates a new instance.
   *
   * @param \Drupal\Component\Plugin\Discovery\DiscoveryInterface $decorated
   *   The parent object implementing DiscoveryInterface that is being
   *   decorated.
   * @param \Psr\Container\ContainerInterface $container
   *   The service container.
   */
  public function __construct(
    protected DiscoveryInterface $decorated,
    protected ContainerInterface $container,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $plugin_definitions = $this->decorated->getDefinitions();

    foreach ($plugin_definitions as $id => $definition) {
      if (!ContainerFactory::supportsAutowiring($definition)) {
        continue;
      }

      $class = DefaultFactory::getPluginClass($id, $definition);
      $constructor = new \ReflectionMethod($class, '__construct');
      $args = [];

      // @todo Figure out how to handle plugins with different constructor
      // signatures.
      foreach (array_slice($constructor->getParameters(), 3) as $pos => $parameter) {
        $service = ltrim((string) $parameter->getType(), '?');
        foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
          $service = (string) $attribute->newInstance()->value;
        }

        if (!$this->container->has($service)) {
          throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), $class));
        }

        $args[$pos] = new Reference($service);
      }

      if ($definition instanceof PluginDefinitionInterface) {
        $definition->constructorServices = $args;
      }
      else {
        $plugin_definitions[$id]['constructor_services'] = $args;
      }
    }

    return $plugin_definitions;
  }

  /**
   * Passes through all unknown calls onto the decorated object.
   */
  public function __call($method, $args): mixed {
    return call_user_func_array([$this->decorated, $method], $args);
  }

}
