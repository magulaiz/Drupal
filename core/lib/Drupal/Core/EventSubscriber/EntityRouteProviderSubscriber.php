<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\entity_display_route\EntityDisplayRouteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Ensures that routes can be provided by entity types.
 */
class EntityRouteProviderSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityRouteProviderSubscriber instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Provides routes on route rebuild time.
   *
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function onDynamicRouteEvent(RouteBuildEvent $event) {
    $route_collection = $event->getRouteCollection();
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
      if ($entity_type->hasRouteProviders()) {
        foreach ($this->entityTypeManager->getRouteProviders($entity_type->id()) as $route_provider) {
          // Allow to both return an array of routes or a route collection,
          // like route_callbacks in the routing.yml file.

          $routes = $route_provider->getRoutes($entity_type);
          if ($routes instanceof RouteCollection) {
            $routes = $routes->all();
          }
          foreach ($routes as $route_name => $route) {
            // Don't override existing routes.
            if (!$route_collection->get($route_name)) {
              $route_collection->add($route_name, $route);
            }
          }
        }
      }
    }
    $viewModeStorage = $this->entityTypeManager->getStorage('entity_view_mode');
    $mode_ids = $viewModeStorage->getQuery()
      ->exists('path')
      ->accessCheck(FALSE)
      ->execute();
    if (\count($mode_ids) === 0) {
      return;
    }
    $modes = $viewModeStorage->loadMultiple($mode_ids);
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $mode */
    foreach ($modes as $id => $mode) {
      $path = $mode->getPath();
      if ($path === NULL) {
        continue;
      }
      [, $display_id] = \explode('.', $id);
      $entity_type_id = $mode->getTargetType();
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $template = $entity_type->getLinkTemplate('canonical');
      if ($template === FALSE) {
        continue;
      }
      $route = new Route(\sprintf('%s/%s', $template, $path));
      $route
        ->addDefaults([
          '_entity_view' => \sprintf('%s.%s', $entity_type_id, $display_id),
          '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
        ])
        ->setRequirement('_entity_access', \sprintf('%s.view', $entity_type_id))
        ->setOption('parameters', [
          $entity_type_id => ['type' => 'entity:' . $entity_type_id],
        ]);

      $canonical = $route_collection->get(\sprintf('entity:%s:canonical', $entity_type_id));
      if ($canonical !== NULL) {
        $requirement = $route->getRequirement($entity_type_id);
        if ($requirement !== NULL) {
          $route->setRequirement($entity_type_id, $requirement);
        }
      }
      $route_collection->add(\sprintf('entity.%s.entity_view_display__%s', $entity_type_id, $display_id), $route);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::DYNAMIC][] = ['onDynamicRouteEvent'];
    return $events;
  }

}
