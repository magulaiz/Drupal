<?php

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Drupal\Core\Entity\RevisionableInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks if the current user has access to newly referenced entities.
 */
class ReferenceAccessConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\FieldItemInterface $value */
    if (!isset($value)) {
      return;
    }
    $id = $value->target_id;
    // '0' or NULL are considered valid empty references.
    if (empty($id)) {
      return;
    }
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $referenced_entity */
    $referenced_entity = $value->entity;
    if ($referenced_entity) {
      $entity = $value->getEntity();
      $check_permission = TRUE;
      if (!$entity->isNew()) {
        $storage = \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId());
        /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
        $revision_id = $entity instanceof RevisionableInterface ? $storage->getLatestRevisionId($entity->id()) : NULL;

        // If the entity supports revisions we want to load the latest revision
        // instead of the default revision.
        if ($revision_id) {
          $existing_entity = $storage->loadRevision($revision_id);
        }
        else {
          $existing_entity = $storage->loadUnchanged($entity->id());
        }

        $referenced_entities = $existing_entity->{$value->getFieldDefinition()->getName()}->referencedEntities();
        // Check permission if we are not already referencing the entity.
        foreach ($referenced_entities as $ref) {
          if ($referenced_entity->id() == $ref->id()) {
            $check_permission = FALSE;
            break;
          }
        }
      }
      // We check that the current user had access to view any newly added
      // referenced entity.
      if ($check_permission && !$referenced_entity->access('view')) {
        $type = $value->getFieldDefinition()->getSetting('target_type');
        $this->context->addViolation($constraint->message, ['%type' => $type, '%id' => $id]);
      }
    }
  }

}
