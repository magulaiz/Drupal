<?php

namespace Drupal\system\EventSubscriber;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters routes to add necessary requirements.
 */
class AccessRouteAlterSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER][] = 'accessAdminMenuBlockPage';
    return $events;
  }

  /**
   * Adds _access_admin_menu_block_page requirement to routes pointing to SystemController::systemAdminMenuBlockPage.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The event to process.
   */
  public function accessAdminMenuBlockPage(RouteBuildEvent $event) {
    $routes = $event->getRouteCollection();
    foreach ($routes as $route) {
      if ($route->hasDefault('_controller') && str_contains($route->getDefault('_controller'), 'Drupal\system\Controller\SystemController::systemAdminMenuBlockPage')) {
        $route->setRequirement('_access_admin_menu_block_page', 'TRUE');
      }
    }
  }

}
