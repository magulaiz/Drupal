<?php

namespace Drupal\user;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds a database key/value store factory for 'user.timestamp.*' collections.
 */
class UserServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    // Use 'user.keyvalue.database' for 'user.timestamp.*' collections as they
    // need unserialized data in the key/value store in order to provide easy
    // Views integration.
    // @see \Drupal\user\UserViewsData::getViewsData()
    $factory_keyvalue = [
      'user.timestamp.access' => 'user.keyvalue.database',
      'user.timestamp.login' => 'user.keyvalue.database',
    ] + $container->getParameter('factory.keyvalue');
    $container->setParameter('factory.keyvalue', $factory_keyvalue);
  }

}
