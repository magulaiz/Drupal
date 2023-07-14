<?php

namespace Drupal\workspaces\Entity\Handler;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Common customizations for most entity types.
 *
 * @internal
 */
class WorkspaceHandlerBase implements WorkspaceHandlerInterface, EntityHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function isEntitySupported(EntityInterface $entity) {
    return is_subclass_of($entity, EntityPublishedInterface::class) && $entity->getEntityType()->isRevisionable();
  }

}
