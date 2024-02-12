<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\EntityInterface;
use Drupal\workspaces\WorkspaceAssociationStorageInterface;
use Drupal\workspaces\WorkspaceInterface;

/**
 * The MongoDB implementation of \Drupal\workspaces\WorkspaceAssociationStorage.
 */
class WorkspaceAssociationStorage extends ContentEntityStorage implements WorkspaceAssociationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function postPush(WorkspaceInterface $workspace) {
    $this->database
      ->delete($this->entityType->getBaseTable())
      ->condition('workspace_association_current_revision.workspace', $workspace->id())
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getTrackedEntities($workspace_id, $all_revisions = FALSE) {
    $query = $this->database->select($this->getBaseTable(), 'base_table');
    $query->fields('base_table', ['workspace_association_all_revisions', 'workspace_association_current_revision']);
    if ($all_revisions) {
      $query->condition('workspace_association_all_revisions.workspace', $workspace_id);
    }
    else {
      $query->condition('workspace_association_current_revision.workspace', $workspace_id);
    }
    $result = $query->execute()->fetchAll();

    $tracked_revisions = [];
    foreach ($result as $record) {
      if ($all_revisions) {
        $revisions = $record->workspace_association_all_revisions;
      }
      else {
        $revisions = $record->workspace_association_current_revision;
      }
      foreach ($revisions as $revision) {
        if (isset($revision['workspace']) && ($revision['workspace'] == $workspace_id)) {
          $target_entity_type_id = $revision['target_entity_type_id'];
          $target_entity_revision_id = $revision['target_entity_revision_id'];
          $target_entity_id = $revision['target_entity_id'];
          $tracked_revisions[$target_entity_type_id][$target_entity_revision_id] = $target_entity_id;
        }
      }
    }

    return $tracked_revisions;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTrackingWorkspaceIds(EntityInterface $entity) {
    $query = $this->database->select($this->getBaseTable(), 'base_table');
    $query
      ->fields('base_table', ['workspace_association_current_revision'])
      ->condition('workspace_association_current_revision.target_entity_type_id', $entity->getEntityTypeId())
      ->condition('workspace_association_current_revision.target_entity_id', (int) $entity->id());
    $result = $query->execute()->fetchAll();

    return $result;
  }

}
