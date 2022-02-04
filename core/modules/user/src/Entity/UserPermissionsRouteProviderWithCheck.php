<?php

namespace Drupal\user\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for the entity permissions form with a custom access check.
 */
class UserPermissionsRouteProviderWithCheck extends UserPermissionsRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getEntityPermissionsRoute(EntityTypeInterface $entity_type): ?Route {
    $route = parent::getEntityPermissionsRoute($entity_type);
    if ($route) {
      $route->setRequirement('_custom_access', '\Drupal\user\Form\UserPermissionsEntityForm::access');
    }
    return $route;
  }

}
