<?php

namespace Drupal\user_filtered_roles_test\EventSubscriber;


use Drupal\user\Entity\Role;
use Drupal\user\Event\RoleFilterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class RoleFilterSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [RoleFilterEvent::class => 'filterRoles'];
  }

  /**
   * Takes a roles array and removes the anonymous and authenticated roles.
   *
   * @param \Drupal\user\Event\RoleFilterEvent $event
   *   The permissions filter list event.
   */
  public function filterRoles(RoleFilterEvent $event):void {
    $event->filter(fn(Role $role_data, string $role_name) => !str_contains($role_name, 'anonymous')  && !str_contains($role_name, 'authenticated'));
  }

}
