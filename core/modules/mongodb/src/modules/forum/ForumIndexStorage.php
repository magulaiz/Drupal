<?php

namespace Drupal\mongodb\modules\forum;

use Drupal\comment\CommentInterface;
use Drupal\forum\ForumIndexStorage as CoreForumIndexStorage;
use Drupal\node\NodeInterface;

/**
 * The MongoDB implementation of \Drupal\forum\ForumIndexStorage.
 */
class ForumIndexStorage extends CoreForumIndexStorage {

  /**
   * {@inheritdoc}
   */
  public function getOriginalTermId(NodeInterface $node) {
    $query = $this->database->select('forum')
      ->fields('forum', ['tid'])
      ->condition('n.nid', (int) $node->id())
      ->orderBy('f.vid', 'DESC')
      ->range(0, 1);
    $query->addMongodbJoin('INNER', 'node', 'vid', 'forum', 'vid', '=', 'n');
    return $query->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function read(array $vids) {
    foreach ($vids as &$vid) {
      $vid = (int) $vid;
    }
    return parent::read($vids);
  }

  /**
   * {@inheritdoc}
   */
  public function updateIndex(NodeInterface $node) {
    $nid = (int) $node->id();

    $query = $this->database->select('comment')
      ->fields('comment', ['cid'])
      ->condition('comment_translations.entity_id', $nid)
      ->condition('comment_translations.field_name', 'comment_forum')
      ->condition('comment_translations.entity_type', 'node')
      ->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED)
      ->condition('comment_translations.default_langcode', TRUE);
    $query->addMongodbJoin('INNER', 'forum_index', 'nid', 'comment', 'comment_translations.entity_id', '=', 'fi');
    $result = $query->execute()->fetchAll();
    $count = count($result);
    if ($count > 0) {
      // Comments exist.
      $last_reply = $this->database->select('comment')
        ->fields('comment', ['cid', 'comment_translations'])
        ->condition('comment_translations.entity_id', $nid)
        ->condition('comment_translations.field_name', 'comment_forum')
        ->condition('comment_translations.entity_type', 'node')
        ->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED)
        ->condition('comment_translations.default_langcode', TRUE)
        ->orderBy('cid', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchObject();

      $last_reply->created = 0;
      foreach ($last_reply->comment_translations as $comment_translation) {
        if (($comment_translation['entity_id'] == $nid) && ($comment_translation['field_name'] == 'comment_forum') &&
            ($comment_translation['entity_type'] == 'node') && ($comment_translation['status'] == (bool) CommentInterface::PUBLISHED) &&
            $comment_translation['default_langcode']) {
          $last_reply->created = $comment_translation['created'];
        }
      }

      $this->database->update('forum_index')
        ->fields([
          'comment_count' => $count,
          'last_comment_timestamp' => $last_reply->created,
        ])
        ->condition('nid', $nid)
        ->execute();
    }
    else {
      // Comments do not exist.
      // @todo This should be actually filtering on the desired node language
      $this->database->update('forum_index')
        ->fields([
          'comment_count' => 0,
          'last_comment_timestamp' => $node->getCreatedTime(),
        ])
        ->condition('nid', $nid)
        ->execute();
    }
  }

}
