<?php

namespace Drupal\user_filtered_permissions_test\EventSubscriber;

use Drupal\user\Event\UserEvents;
use Drupal\user\Event\PermissionsListFilterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class PermissionsListFilterSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [UserEvents::PERMISSIONS_LIST_FILTER => 'processPermissions'];
  }

  /**
   * Takes a permissions array and removes all keys but a, b, c.
   *
   * @param \Drupal\user\Event\PermissionsListFilterEvent $event
   */
  public function processPermissions(PermissionsListFilterEvent $event) {
    $permissions = $event->getPermissions();
    foreach ($permissions as $key => $value) {
      if (!in_array($key, ['a', 'b', 'c'])) {
        unset($permissions[$key]);
      }
    }
    $permissions = array_filter($permissions, fn($key) => in_array($key, ['a', 'b', 'c']), ARRAY_FILTER_USE_KEY);
    $event->setPermissions($permissions);
  }


}
