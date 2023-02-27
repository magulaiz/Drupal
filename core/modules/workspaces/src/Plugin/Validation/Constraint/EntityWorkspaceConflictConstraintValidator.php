<?php

namespace Drupal\workspaces\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\workspaces\WorkspaceAssociationInterface;
use Drupal\workspaces\WorkspaceManagerInterface;
use Drupal\workspaces\WorkspaceRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the EntityWorkspaceConflict constraint.
 */
class EntityWorkspaceConflictConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Constructs an EntityUntranslatableFieldsConstraintValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\workspaces\WorkspaceManagerInterface $workspaceManager
   *   The workspace manager service.
   * @param \Drupal\workspaces\WorkspaceAssociationInterface $workspaceAssociation
   *   The workspace association service.
   * @param \Drupal\workspaces\WorkspaceRepositoryInterface $workspaceRepository
   *   The Workspace repository service.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager, protected WorkspaceManagerInterface $workspaceManager, protected WorkspaceAssociationInterface $workspaceAssociation, protected WorkspaceRepositoryInterface $workspaceRepository)
  {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('workspaces.manager'),
      $container->get('workspaces.association'),
      $container->get('workspaces.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if (isset($entity) && !$entity->isNew()) {
      $active_workspace = $this->workspaceManager->getActiveWorkspace();

      // Get the latest revision of the entity in order to check if it's being
      // edited in a different workspace.
      $latest_revision = $this->workspaceManager->executeOutsideWorkspace(function () use ($entity) {
        $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
        return $storage->loadRevision($storage->getLatestRevisionId($entity->id()));
      });

      // If the latest revision of the entity is tracked in a workspace, it can
      // only be edited in that workspace or one of its descendants.
      if ($latest_revision_workspace = $latest_revision->workspace->entity) {
        $descendants_and_self = $this->workspaceRepository->getDescendantsAndSelf($latest_revision_workspace->id());

        if (!$active_workspace || !in_array($active_workspace->id(), $descendants_and_self, TRUE)) {
          $this->context->buildViolation($constraint->message)
            ->setParameter('%label', $latest_revision_workspace->label())
            ->addViolation();
        }
      }
    }
  }

}
