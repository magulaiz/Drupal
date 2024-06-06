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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    if (!method_exists(static::class, '__construct')) {
      return new static($container, $configuration, $plugin_id, $plugin_definition);
    }

    $args = [];
    $constructor = new \ReflectionMethod(static::class, '__construct');
    foreach ($constructor->getParameters() as $parameter) {
      $args[$parameter->getName()] = match ($parameter->getName()) {
        'configuration' => $configuration,
        // Allow constructor to use either snake_case or camelCase.
        'plugin_id', 'pluginId' => $plugin_id,
        'plugin_definition', 'pluginDefinition' => $plugin_definition,
        default => (static function () use ($container, $parameter) {
          $service = ltrim((string) $parameter->getType(), '?');
          foreach ($parameter->getAttributes(Autowire::class) as $attribute) {
            $service = (string) $attribute->newInstance()->value;
          }

          if (!$container->has($service)) {
            throw new AutowiringFailedException($service, sprintf('Cannot autowire service "%s": argument "$%s" of method "%s::_construct()", you should configure its value explicitly.', $service, $parameter->getName(), static::class));
          }

          return $container->get($service);
        })(),
      };
    }

    return new static(...$args);
  }

}
