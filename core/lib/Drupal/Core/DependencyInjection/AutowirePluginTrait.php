<?php

declare(strict_types=1);

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;

/**
 * Allows plugins extending ContainerFactoryPluginInterface to use autowiring.
 *
 * @see \Drupal\Core\Plugin\ContainerFactoryPluginInterface
 */
trait AutowirePluginTrait {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $args = [$configuration, $plugin_id, $plugin_definition];

    if (method_exists(static::class, '__construct')) {
      $constructor = new \ReflectionMethod(static::class, '__construct');
      foreach (array_slice($constructor->getParameters(), 3) as $parameter) {
        $service = ltrim((string) $parameter->getType(), '?');
        foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
          $service = (string) $attribute->newInstance()->value;
        }

        if (!$container->has($service)) {
          throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), static::class));
        }

        $args[] = $container->get($service);
      }
    }

    return new static(...$args);
  }

}
