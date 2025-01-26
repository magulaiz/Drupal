<?php

declare(strict_types=1);

namespace Drupal\sqlite;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Service provider for conditionally setting service overrides.
 */
final class SqliteServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    if ($container->hasDefinition('logger.dblog')) {
      $container->setDefinition(
        'sqlite.logger.dblog',
        (clone $container->getDefinition('logger.dblog'))
          ->setArgument(0, new Reference('database'))
          ->setTags([])
          ->setPublic(FALSE)
      );
    }
  }

}
