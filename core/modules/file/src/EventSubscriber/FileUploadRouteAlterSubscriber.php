<?php

namespace Drupal\file\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Route Subscriber for altering file upload routes.
 */
class FileUploadRouteAlterSubscriber implements EventSubscriberInterface {

  /**
   * Creates a new RestRouteAlterSubscriber.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Alters the rest file upload route.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function alterFileUploadRoutes(RouteBuildEvent $event): void {
    if (!$this->moduleHandler->moduleExists('rest')) {
      return;
    }
    $collection = $event->getRouteCollection();
    if ($route = $collection->get('rest.file.upload.POST')) {
      // Add the file upload access check.
      $route->setRequirement('_file_upload_access', 'TRUE');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Priority -20, to run after
    // \Drupal\rest\EventSubscriber\EntityResourcePostRouteSubscriber which has
    // priority -1.
    $events[RoutingEvents::ALTER][] = ['alterFileUploadRoutes', -20];
    return $events;
  }

}
