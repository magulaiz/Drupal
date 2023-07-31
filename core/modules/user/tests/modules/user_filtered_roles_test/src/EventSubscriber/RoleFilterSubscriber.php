<?php

namespace Drupal\user_filtered_roles_test\EventSubscriber;

use Drupal\user\Event\RoleFilterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class RoleFilterSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [RoleFilterEvent::class => 'filterRoles'];
  }

  /**
   * Takes a permissions array and removes all keys but a, b, c.
   *
   * @param \Drupal\user\Event\RoleFilterEvent $event
   *   The permissions filter list event.
   */
  public function filterRoles(RoleFilterEvent $event):void {
    $stop = 'here';
    $event->filter(fn(array $role_data, string $role_name) => substr($role_name, 0, 1) !== 'anonymous' && substr($role_name, 0, 1) !== 'authenticated');
  }

}
