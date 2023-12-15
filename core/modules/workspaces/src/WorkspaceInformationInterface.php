<?php

namespace Drupal\workspaces;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an interface for workspace support information.
 */
interface WorkspaceInformationInterface {

  /**
   * Indicates that CRUD operations for a non-supported entity type are allowed.
   */
  const IGNORED = 'ignored';

  /**
   * Indicates that an entity type can belong to a workspace.
   */
  const SUPPORTED = 'supported';

  /**
   * Indicates that some entities of a certain type may belong to a workspace.
   *
   * Entity types that need to check workspace support on a per-entity basis
   * can do so by implementing a 'workspace' entity type handler.
   *
   * @see \Drupal\workspaces\Entity\Handler\BlockContentWorkspaceHandler
   */
  const SUPPORTED_CUSTOM = 'supported_custom';

  /**
   * Determines if an individual entity can belong to a workspace.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if the entity can belong to a workspace, FALSE otherwise.
   */
  public function isEntitySupported(EntityInterface $entity);

  /**
   * Determines if an entity type can belong to a workspace.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type to check.
   *
   * @return bool
   *   TRUE if the entity type can belong to a workspace, FALSE otherwise.
   */
  public function isEntityTypeSupported(EntityTypeInterface $entity_type);

  /**
   * Returns an array of entity types that can belong to workspaces.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   The entity types that can belong to workspaces.
   */
  public function getSupportedEntityTypes();

  /**
   * Determines if CRUD operations for an entity are allowed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if CRUD operations of an entity type can safely be done inside a
   *   workspace, without impacting the Live site, FALSE otherwise.
   */
  public function isEntityIgnored(EntityInterface $entity);

  /**
   * Determines if CRUD operations for a non-supported entity type are allowed.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type to check.
   *
   * @return bool
   *   TRUE if CRUD operations of an entity type can safely be done inside a
   *   workspace, without impacting the Live site, FALSE otherwise.
   */
  public function isEntityTypeIgnored(EntityTypeInterface $entity_type);

}
