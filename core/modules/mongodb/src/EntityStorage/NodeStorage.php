<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorage as CoreNodeStorage;

/**
 * The MongoDB implementation of \Drupal\node\NodeStorage.
 */
class NodeStorage extends CoreNodeStorage {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(NodeInterface $node) {
    $results = $this->database->select('node')
      ->fields('node', ['node_all_revisions.vid'])
      ->condition('nid', (int) $node->id())
      ->execute()
      ->fetchCol();
    $result = reset($results);
    sort($result);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    $results = $this->database->select('node', 'n')
      ->fields('n', ['node_all_revisions'])
      ->condition('node_all_revisions.uid', (int) $account->id())
      ->execute()
      ->fetchAll();

    $vids = [];
    foreach ($results as $node) {
      foreach ($node->node_all_revisions as $revision) {
        if ($revision['uid'] == $account->id()) {
          $vids[] = (int) $revision['vid'];
        }
      }
    }

    $vids = array_unique($vids);
    sort($vids);
    return $vids;
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(NodeInterface $node) {
    $node_all_revisions = $this->database->select('node', 'n')
      ->fields('n', ['node_all_revisions'])
      ->condition('nid', (int) $node->id())
      ->execute()
      ->fetchField();
    $count = 0;
    foreach ($node_all_revisions as $node_all_revision) {
      if (isset($node_all_revision['default_langcode']) && ($node_all_revision['default_langcode'] == 1)) {
        $count++;
      }
    }
    return $count;
  }

}
