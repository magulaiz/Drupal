<?php

namespace Drupal\mongodb\modules\forum;

use Drupal\Core\Session\AccountInterface;
use Drupal\forum\ForumManager as CoreForumManager;
use MongoDB\BSON\UTCDateTime;

/**
 * The MongoDB implementation of \Drupal\forum\ForumManager.
 */
class ForumManager extends CoreForumManager {

  /**
   * {@inheritdoc}
   */
  public function getTopics($tid, AccountInterface $account) {
    $config = $this->configFactory->get('forum.settings');
    $forum_per_page = $config->get('topics.page_limit');
    $sortby = $config->get('topics.order');

    $header = [
      ['data' => $this->t('Topic'), 'field' => 'f.title'],
      ['data' => $this->t('Replies'), 'field' => 'f.comment_count'],
      ['data' => $this->t('Last reply'), 'field' => 'f.last_comment_timestamp'],
    ];

    $order = $this->getTopicOrder($sortby);
    for ($i = 0; $i < count($header); $i++) {
      if ($header[$i]['field'] == $order['field']) {
        $header[$i]['sort'] = $order['sort'];
      }
    }

    $query = $this->connection->select('forum_index', 'f')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('f');
    $query
      ->condition('f.tid', (int) $tid)
      ->addTag('node_access')
      ->addMetaData('base_table', 'forum_index')
      ->orderBy('f.sticky', 'DESC')
      ->orderByHeader($header)
      ->limit($forum_per_page);

    $count_query = $this->connection->select('forum_index', 'f');
    $count_query->condition('f.tid', (int) $tid);
    $count_query->addTag('node_access');
    $count_query->addMetaData('base_table', 'forum_index');

    $query->setCountQuery($count_query);
    $result = $query->execute()->fetchAll();
    $nids = [];
    foreach ($result as $record) {
      $nids[] = (int) $record->nid;
    }
    if ($nids) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      $query = $this->connection->select('node', 'n')
        ->extend('Drupal\Core\Database\Query\TableSortExtender');
      $query->fields('n', ['nid']);

      $query->addMongodbJoin('LEFT', 'comment_entity_statistics', 'entity_id', 'node', 'nid', '=', 'ces', [['field' => 'field_name', 'value' => 'comment_forum'], ['field' => 'entity_type', 'value' => 'node']]);
      $query->fields('ces', [
        'cid',
        'last_comment_uid',
        'last_comment_timestamp',
        'comment_count',
      ]);

      $query->addMongodbJoin('LEFT', 'forum_index', 'nid', 'node', 'nid', '=', 'f');
      $query->addField('f', 'tid', 'forum_tid');

      $query->addMongodbJoin('LEFT', 'users', 'user_translations.uid', 'node', 'node_current_revision.uid', '=', 'u', [['field' => 'user_translations.default_langcode', 'value' => TRUE]]);
      $query->addField('u', 'name');

      $query->addMongodbJoin('LEFT', 'users', 'user_translations.uid', 'ces', 'last_comment_uid', '=', 'u2', [['field' => 'user_translations.default_langcode', 'value' => TRUE]]);

      $query
        ->orderBy('f.sticky', 'DESC')
        ->orderByHeader($header)
        ->condition('n.nid', $nids, 'IN')
        ->condition('n.node_current_revision.default_langcode', TRUE);

      $result = [];
      foreach ($query->execute() as $row) {
        $topic = $nodes[$row->nid];
        $topic->comment_mode = $topic->comment_forum->status;

        foreach ($row as $key => $value) {
          $topic->{$key} = (is_array($value) ? reset($value) : $value);
        }
        $result[] = $topic;
      }
    }
    else {
      $result = [];
    }

    $topics = [];
    $first_new_found = FALSE;
    foreach ($result as $topic) {
      if ($account->isAuthenticated()) {
        // A forum is new if the topic is new, or if there are new comments since
        // the user's last visit.
        if ($topic->forum_tid != $tid) {
          $topic->new = 0;
        }
        else {
          $history = $this->lastVisit($topic->id(), $account);
          $topic->new_replies = $this->commentManager->getCountNewComments($topic, 'comment_forum', $history);
          $topic->new = $topic->new_replies || ($topic->last_comment_timestamp > $history);
        }
      }
      else {
        // Do not track "new replies" status for topics if the user is anonymous.
        $topic->new_replies = 0;
        $topic->new = 0;
      }

      // Make sure only one topic is indicated as the first new topic.
      $topic->first_new = FALSE;
      if ($topic->new != 0 && !$first_new_found) {
        $topic->first_new = TRUE;
        $first_new_found = TRUE;
      }

      if ($topic->comment_count > 0) {
        $last_reply = new \stdClass();
        $last_reply->created = $topic->last_comment_timestamp;
        $last_reply->name = $topic->last_comment_name;
        $last_reply->uid = $topic->last_comment_uid;
        $topic->last_reply = $last_reply;
      }
      $topics[$topic->id()] = $topic;
    }

    return ['topics' => $topics, 'header' => $header];

  }

  /**
   * {@inheritdoc}
   */
  protected function getLastPost($tid) {
    if (!empty($this->lastPostData[$tid])) {
      return $this->lastPostData[$tid];
    }
    // Query "Last Post" information for this forum.
    $query = $this->connection->select('node', 'n');
    $query->addMongodbJoin('LEFT', 'forum', 'vid', 'node', 'node_current_revision.vid', '=', 'f', [['field' => 'tid', 'value' => (int) $tid]]);
    $query->addMongodbJoin('LEFT', 'comment_entity_statistics', 'entity_id', 'node', 'nid', '=', 'ces', [['field' => 'field_name', 'value' => 'comment_forum'], ['field' => 'entity_type', 'value' => 'node']]);
    $query->addMongodbJoin('LEFT', 'users', 'user_translations.uid', 'ces', 'last_comment_uid', '=', 'u', [['field' => 'user_translations.default_langcode', 'value' => TRUE]]);

    $topic = $query
      ->fields('ces', ['last_comment_timestamp', 'last_comment_uid', 'last_comment_name'])
      ->fields('u', ['user_translations'])
      ->condition('node_current_revision.status', TRUE)
      ->range(0, 1)
      ->addTag('node_access')
      ->execute()
      ->fetchObject();

    // Build the last post information.
    $last_post = new \stdClass();
    if (!empty($topic->last_comment_timestamp)) {
      $last_post->created = $topic->last_comment_timestamp;
      $last_post->name = $topic->last_comment_name;
      $last_post->uid = $topic->last_comment_uid;
    }

    $this->lastPostData[$tid] = $last_post;
    return $last_post;
  }

  /**
   * {@inheritdoc}
   */
  protected function getForumStatistics($tid) {
    if (empty($this->forumStatistics)) {
      // Prime the statistics.
      $query = $this->connection->select('node', 'n')
        ->fields('n', ['nid']);
      $query->addMongodbJoin('LEFT', 'comment_entity_statistics', 'entity_id', 'node', 'nid', '=', 'ces', [['field' => 'field_name', 'value' => 'comment_forum'], ['field' => 'entity_type', 'value' => 'node']]);
      $query->addMongodbJoin('LEFT', 'forum', 'vid', 'node', 'node_current_revision.vid', '=', 'f');
      $result = $query
        ->fields('ces', ['comment_count'])
        ->fields('f', ['tid'])
        ->condition('node_current_revision.status', TRUE)
        ->condition('node_current_revision.default_langcode', TRUE)
        ->addTag('node_access')
        ->execute()
        ->fetchAll();

      if (empty($result)) {
        $forum_statistics = new \stdClass();
        $forum_statistics->topic_count = 0;
        $forum_statistics->comment_count = 0;
        $this->forumStatistics[$tid] = $forum_statistics;
      }
      else {
        $tids = [];
        foreach ($result as $row) {
          if (isset($row->f_tid) && is_array($row->f_tid)) {
            $tid = reset($row->f_tid);
            if (isset($tids[$tid]['topic_count'])) {
              $tids[$tid]['topic_count'] = $tids[$tid]['topic_count'] + 1;
            }
            else {
              $tids[$tid]['topic_count'] = 1;
            }
            if (isset($row->ces_comment_count) && is_array($row->ces_comment_count)) {
              $comment_count = reset($row->ces_comment_count);
            }
            else {
              $comment_count = 0;
            }
            if (isset($tids[$tid]['comment_count'])) {
              $tids[$tid]['comment_count'] = $tids[$tid]['comment_count'] + $comment_count;
            }
            else {
              $tids[$tid]['comment_count'] = $comment_count;
            }
          }
        }

        foreach ($tids as $tid => $data) {
          $forum_statistics = new \stdClass();
          $forum_statistics->topic_count = $data['topic_count'];
          $forum_statistics->comment_count = $data['comment_count'];
          $this->forumStatistics[$tid] = $forum_statistics;
        }
      }
    }

    if (!empty($this->forumStatistics[$tid])) {
      return $this->forumStatistics[$tid];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function unreadTopics($term, $uid) {
    $history_read_limit = new UTCDateTime(HISTORY_READ_LIMIT * 1000);

    $query = $this->connection->select('node', 'n');
    $query->addMongodbJoin('LEFT', 'forum', 'vid', 'node', 'node_current_revision.vid', '=', 'f', [['field' => 'tid', 'value' => (int) $term]]);
    $query->addMongodbJoin('LEFT', 'history', 'nid', 'node', 'nid', '=', 'n', [['field' => 'uid', 'value' => (int) $uid]]);
    $result = $query
      ->condition('node_current_revision.status', TRUE)
      ->condition('node_current_revision.default_langcode', TRUE)
      ->condition('node_current_revision.created', $history_read_limit, '>')
      ->isNull('h.nid')
      ->addTag('node_access')
      ->execute()
      ->fetchAll();

    return count($result);
  }

}
