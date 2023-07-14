<?php

namespace Drupal\workspaces;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * General service for workspace support information.
 */
class WorkspaceInformation implements WorkspaceInformationInterface {

  /**
   * An array of which entity types are supported.
   *
   * @var string[]
   */
  protected $supported = [];

  /**
   * An array of which entity types are ignored.
   *
   * @var string[]
   */
  protected $ignored = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WorkspaceInformation.
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
  public function isEntitySupported(EntityInterface $entity) {
    $entity_type = $entity->getEntityType();

    if ($entity_type->get('workspace') === static::SUPPORTED_CUSTOM) {
      /** @var \Drupal\workspaces\Entity\Handler\WorkspaceHandlerInterface $handler */
      $handler = $this->entityTypeManager->getHandler($entity_type->id(), 'workspace');

      return $handler->isEntitySupported($entity);
    }

    return $this->isEntityTypeSupported($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function isEntityTypeSupported(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    if (!isset($this->supported[$entity_type_id])) {
      if ($workspace_support = $entity_type->get('workspace')) {
        $this->supported[$entity_type_id] = $workspace_support === static::SUPPORTED || $workspace_support === static::SUPPORTED_CUSTOM;
      }
      else {
        // Add a fallback check for entity types which might not have gone through
        // the entity type manager's building phase.
        $this->supported[$entity_type_id] = $entity_type->entityClassImplements(EntityPublishedInterface::class) && $entity_type->isRevisionable();
      }
    }
    return $this->supported[$entity_type_id];
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedEntityTypes() {
    $entity_types = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($this->isEntityTypeSupported($entity_type)) {
        $entity_types[$entity_type_id] = $entity_type;
      }
    }
    return $entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function isEntityIgnored(EntityInterface $entity) {
    $entity_type = $entity->getEntityType();

    if ($entity_type->get('workspace') === static::SUPPORTED_CUSTOM) {
      /** @var \Drupal\workspaces\Entity\Handler\WorkspaceHandlerInterface $handler */
      $handler = $this->entityTypeManager->getHandler($entity_type->id(), 'workspace');

      return $handler->isEntitySupported($entity) === FALSE;
    }

    return $this->isEntityTypeIgnored($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function isEntityTypeIgnored(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    if (!isset($this->ignored[$entity_type_id])) {
      $this->ignored[$entity_type_id] = $entity_type->get('workspace') === static::IGNORED;
    }
    return $this->ignored[$entity_type_id];
  }

}
