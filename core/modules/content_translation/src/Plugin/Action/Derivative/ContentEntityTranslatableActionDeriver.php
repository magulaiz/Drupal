<?php

namespace Drupal\content_translation\Plugin\Action\Derivative;

use Drupal\Core\Action\Plugin\Action\Derivative\EntityActionDeriverBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an action deriver that finds translatable content entity types.
 *
 * @see \Drupal\content_translation\Plugin\Action\CreateEntityTranslationAction
 */
class ContentEntityTranslatableActionDeriver extends EntityActionDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function isApplicable(EntityTypeInterface $entity_type) {
    return $entity_type->entityClassImplements(ContentEntityInterface::class) && $entity_type->isTranslatable();
  }

}
