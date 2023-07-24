<?php

namespace Drupal\user_filtered_permissions_test\EventSubscriber;

use Drupal\user\Event\PermissionsListFilterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber for testing PermissionsListFilterEvent.
 */
class PermissionsListFilterSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [PermissionsListFilterEvent::class => 'filterPermissions'];
  }

  /**
   * Takes a permissions array and removes all keys but a, b, c.
   *
   * @param \Drupal\user\Event\PermissionsListFilterEvent $event
   *   The permissions filter list event.
   */
  public function filterPermissions(PermissionsListFilterEvent $event) {
    $event->filter(fn(string $permission) => in_array($permission, ['a', 'b', 'c']));
  }

}
