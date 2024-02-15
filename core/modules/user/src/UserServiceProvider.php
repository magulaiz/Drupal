<?php

declare(strict_types = 1);

namespace Drupal\user;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Service provider for User module.
 */
final class UserServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container
      ->addCompilerPass(new UserPermissionProviderCompilerPass());
  }

}
