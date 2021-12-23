<?php

namespace Drupal\user\Entity;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for the user entity.
 */
class UserPermissionsRouteProvider implements EntityRouteProviderInterface, EntityHandlerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new UserPermissionsRouteProvider.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();

    if (!$entity_type->hasLinkTemplate('permission-form')) {
      return $route_collection;
    }

    if (!$bundle_of_id = $entity_type->getBundleOf()) {
      return $route_collection;
    }

    $bundle_type_id = $entity_type->id();
    $route = new Route(
      $entity_type->getLinkTemplate('permission-form'),
      [
        '_title' => 'Manage permissions',
        '_form' => 'Drupal\user\Form\UserPermissionsBundleForm',
        'entity_type_id' => $bundle_of_id,
        'bundle_entity_type' => $bundle_type_id,
      ],
      [
        '_permission' => 'administer permissions',
        '_custom_access' => '\Drupal\user\Form\UserPermissionsBundleForm::access',
      ],
      [
        // Indicate that Drupal\Core\Entity\EntityBundleRouteEnhancer should
        // set the bundle parameter.
        '_field_ui' => TRUE,
        'parameters' => [
          $bundle_type_id => [
            'type' => "entity:$bundle_type_id",
            'with_config_overrides' => TRUE,
          ],
        ],
        '_admin_route' => TRUE,
      ]
    );

    $route_collection->add("entity.$bundle_type_id.permission_form", $route);

    return $route_collection;
  }

}
