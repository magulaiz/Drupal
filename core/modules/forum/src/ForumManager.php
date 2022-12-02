<?php

namespace Drupal\forum;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\comment\CommentManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Provides forum manager service.
 */
class ForumManager implements ForumManagerInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait {
    __wakeup as defaultWakeup;
    __sleep as defaultSleep;
  }

  /**
   * Forum sort order, newest first.
   */
  const NEWEST_FIRST = 1;

  /**
   * Forum sort order, oldest first.
   */
  const OLDEST_FIRST = 2;

  /**
   * Forum sort order, posts with most comments first.
   */
  const MOST_POPULAR_FIRST = 3;

  /**
   * Forum sort order, posts with the least comments first.
   */
  const LEAST_POPULAR_FIRST = 4;

  /**
   * Forum settings config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  protected $commentManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Array of last post information keyed by forum (term) id.
   *
   * @var array
   */
  protected $lastPostData = [];

  /**
   * Array of forum statistics keyed by forum (term) id.
   *
   * @var array
   */
  protected $forumStatistics = [];

  /**
   * Array of forum children keyed by parent forum (term) id.
   *
   * @var array
   */
  protected $forumChildren = [];

  /**
   * Array of history keyed by nid.
   *
   * @var array
   */
  protected $history = [];

  /**
   * Cached forum index.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $index;

  /**
   * Constructs the forum manager service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The current database connection.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager service.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Session\AccountInterface|null $current_user
   *   The current logged in user. This parameter is required as of drupal:10.1.0 and
   *   trigger a fatal error if not passed in drupal:11.0.0.
   *
   * @see https://www.drupal.org/node/145353
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, Connection $connection, TranslationInterface $string_translation, CommentManagerInterface $comment_manager, EntityFieldManagerInterface $entity_field_manager, AccountInterface $current_user = NULL) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
    $this->stringTranslation = $string_translation;
    $this->commentManager = $comment_manager;
    $this->entityFieldManager = $entity_field_manager;
    if ($current_user == NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $current_user argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/145353.', E_USER_DEPRECATED);
      $current_user = \Drupal::currentUser();
    }
    $this->currentUser = $current_user;

  }

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
      ->extend(PagerSelectExtender::class)
      ->extend(TableSortExtender::class);
    $query->fields('f');
    $query
      ->condition('f.tid', $tid)
      ->addTag('node_access')
      ->addMetaData('base_table', 'forum_index')
      ->orderBy('f.sticky', 'DESC')
      ->orderByHeader($header)
      ->limit($forum_per_page);

    $count_query = $this->connection->select('forum_index', 'f');
    $count_query->condition('f.tid', $tid);
    $count_query->addExpression('COUNT(*)');
    $count_query->addTag('node_access');
    $count_query->addMetaData('base_table', 'forum_index');

    $query->setCountQuery($count_query);
    $result = $query->execute();
    $nids = [];
    foreach ($result as $record) {
      $nids[] = $record->nid;
    }
    if ($nids) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      $query = $this->connection->select('node_field_data', 'n')
        ->extend(TableSortExtender::class);
      $query->fields('n', ['nid']);

      $query->join('comment_entity_statistics', 'ces', "[n].[nid] = [ces].[entity_id] AND [ces].[field_name] = 'comment_forum' AND [ces].[entity_type] = 'node'");
      $query->fields('ces', [
        'cid',
        'last_comment_uid',
        'last_comment_timestamp',
        'comment_count',
      ]);

      $query->join('forum_index', 'f', '[f].[nid] = [n].[nid]');
      $query->addField('f', 'tid', 'forum_tid');

      $query->join('users_field_data', 'u', '[n].[uid] = [u].[uid] AND [u].[default_langcode] = 1');
      $query->addField('u', 'name');

      $query->join('users_field_data', 'u2', '[ces].[last_comment_uid] = [u2].[uid] AND [u].[default_langcode] = 1');

      $query->addExpression('CASE [ces].[last_comment_uid] WHEN 0 THEN [ces].[last_comment_name] ELSE [u2].[name] END', 'last_comment_name');

      $query
        ->orderBy('f.sticky', 'DESC')
        ->orderByHeader($header)
        ->condition('n.nid', $nids, 'IN')
        // @todo This should be actually filtering on the desired node language
        //   and just fall back to the default language.
        ->condition('n.default_langcode', 1);

      $result = [];
      foreach ($query->execute() as $row) {
        $topic = $nodes[$row->nid];
        $topic->comment_mode = $topic->comment_forum->status;

        foreach ($row as $key => $value) {
          $topic->{$key} = $value;
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
   * Gets topic sorting information based on an integer code.
   *
   * @param int $sortby
   *   One of the following integers indicating the sort criteria:
   *   - ForumManager::NEWEST_FIRST: Date - newest first.
   *   - ForumManager::OLDEST_FIRST: Date - oldest first.
   *   - ForumManager::MOST_POPULAR_FIRST: Posts with the most comments first.
   *   - ForumManager::LEAST_POPULAR_FIRST: Posts with the least comments first.
   *
   * @return array
   *   An array with the following values:
   *   - field: A field for an SQL query.
   *   - sort: 'asc' or 'desc'.
   */
  protected function getTopicOrder($sortby) {
    switch ($sortby) {
      case static::NEWEST_FIRST:
        return ['field' => 'f.last_comment_timestamp', 'sort' => 'desc'];

      case static::OLDEST_FIRST:
        return ['field' => 'f.last_comment_timestamp', 'sort' => 'asc'];

      case static::MOST_POPULAR_FIRST:
        return ['field' => 'f.comment_count', 'sort' => 'desc'];

      case static::LEAST_POPULAR_FIRST:
        return ['field' => 'f.comment_count', 'sort' => 'asc'];

    }
  }

  /**
   * Gets the last time the user viewed a node.
   *
   * @param int $nid
   *   The node ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account to fetch last time for.
   *
   * @return int
   *   The timestamp when the user last viewed this node, if the user has
   *   previously viewed the node; otherwise HISTORY_READ_LIMIT.
   */
  protected function lastVisit($nid, AccountInterface $account) {
    if (empty($this->history[$nid])) {
      $result = $this->connection->select('history', 'h')
        ->fields('h', ['nid', 'timestamp'])
        ->condition('uid', $account->id())
        ->execute();
      foreach ($result as $t) {
        $this->history[$t->nid] = $t->timestamp > HISTORY_READ_LIMIT ? $t->timestamp : HISTORY_READ_LIMIT;
      }
    }
    return $this->history[$nid] ?? HISTORY_READ_LIMIT;
  }

  /**
   * Provides the last post information for the given forum tid.
   *
   * @param int $tid
   *   The forum tid.
   *
   * @return object
   *   The last post for the given forum.
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use getLastPostData() instead.
   *
   * @see https://www.drupal.org/project/drupal/issues/145353
   */
  protected function getLastPost($tid) {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use getLastPostData() instead. See https://www.drupal.org/node/145353.', E_USER_DEPRECATED);
    if (!empty($this->lastPostData[$tid])) {
      return $this->lastPostData[$tid];
    }
    $data = $this->getLastPostData([$tid]);
    return reset($data);
  }

  /**
   * Provides the last post information for the given forum tids.
   *
   * @param int[] $tids
   *   The forum tids.
   *
   * @return \stdClass[]
   *   The last post information for the given forums.
   */
  protected function getLastPostData(array $tids) {
    // Check if all tids already have post info. We assume no duplicate tids.
    $tids_as_keys = array_flip($tids);
    $known_data = array_intersect_key($this->lastPostData, $tids_as_keys);
    if (count($known_data) < count($tids)) {
      $unknown_tids = array_diff($tids, array_keys($this->lastPostData));

      // Query "Last Post" information. Only add the node table to the query if
      // this is necessary for filtering access-restricted records.
      if ($this->currentUserCanViewAllNodes()) {
        $query = $this->connection->select('forum_index', 'f');
      }
      else {
        $query = $this->connection->select('node', 'n')
          ->addTag('node_access');
        $query->join('forum_index', 'f', 'n.nid = f.nid');
      }
      $query->join('comment_entity_statistics', 'ces', "f.nid = ces.entity_id AND ces.field_name = 'comment_forum' AND ces.entity_type = 'node'");
      $query->join('users_field_data', 'u', 'ces.last_comment_uid = u.uid AND u.default_langcode = 1');
      $query->addField('f', 'tid');
      $query->addExpression('COALESCE(ces.last_comment_name, u.name)', 'last_comment_name');

      $topics = $query
        ->fields('ces', ['last_comment_timestamp', 'last_comment_uid'])
        ->condition('f.tid', $unknown_tids, 'IN')
        ->orderBy('f.last_comment_timestamp', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchAllAssoc('tid');

      // Build the last post information.
      foreach ($unknown_tids as $tid) {
        $this->lastPostData[$tid] = new \stdClass();
        if (!empty($topics[$tid]->last_comment_timestamp)) {
          $this->lastPostData[$tid]->created = $topics[$tid]->last_comment_timestamp;
          $this->lastPostData[$tid]->name = $topics[$tid]->last_comment_name;
          $this->lastPostData[$tid]->uid = $topics[$tid]->last_comment_uid;
        }
      }

      $known_data = array_intersect_key($this->lastPostData, $tids_as_keys);
    }
    return $known_data;
  }

  /**
   * Provides statistics for a forum.
   *
   * This will prime statistics for all known forums, to minimize queries.
   *
   * @param int $tid
   *   The forum tid.
   *
   * @return object|null
   *   Statistics for the given forum if statistics exist, else NULL.
   */
  protected function getForumStatistics($tid) {
    if (empty($this->forumStatistics)) {
      // Prime the statistics. Only add the node table to the query if this is
      // necessary for filtering access-restricted records.
      if ($this->currentUserCanViewAllNodes()) {
        $query = $this->connection->select('forum_index', 'f');
      }
      else {
        $query = $this->connection->select('node', 'n')
          ->addTag('node_access');
        $query->join('forum_index', 'f', 'n.nid = f.nid');
      }
      $query->addExpression('COUNT(f.nid)', 'topic_count');
      $query->addExpression('SUM(f.comment_count)', 'comment_count');
      $this->forumStatistics = $query
        ->fields('f', ['tid'])
        ->groupBy('tid')
        ->orderBy('NULL')
        ->execute()
        ->fetchAllAssoc('tid');
    }

    if (!empty($this->forumStatistics[$tid])) {
      return $this->forumStatistics[$tid];
    }
  }

  /**
   * Checks if the current user can view all nodes.
   *
   * This is a private method which will/may ONLY be used for modifying queries
   * in a way that does not alter the returned results. (Under this condition it
   * is not a huge problem that this method calls a global, non-injected
   * function; there is no real 'injectable' alternative for it yet.)
   */
  private function currentUserCanViewAllNodes() {
    $account = $this->currentUser;
    return $account->hasPermission('bypass node access') || node_access_view_all_nodes($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren($vid, $tid) {
    if (!empty($this->forumChildren[$tid])) {
      return $this->forumChildren[$tid];
    }
    $forums = [];
    $_forums = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vid, $tid, NULL, TRUE);
    // Prime last post details for the forums. Unlike getForumStatistics() we
    // only query data for the forums we actually need.
    $tids = [];
    foreach ($_forums as $forum) {
      $tids[] = $forum->id();
    }
    $last_post_data = $this->getLastPostData($tids);
    foreach ($_forums as $forum) {
      // Merge in the topic and post counters.
      if (($count = $this->getForumStatistics($forum->id()))) {
        $forum->num_topics = $count->topic_count;
        $forum->num_posts = $count->topic_count + $count->comment_count;
      }
      else {
        $forum->num_topics = 0;
        $forum->num_posts = 0;
      }

      // Merge in last post details.
      $forum->last_post = $last_post_data[$forum->id()];
      $forums[$forum->id()] = $forum;
    }

    $this->forumChildren[$tid] = $forums;
    return $forums;
  }

  /**
   * {@inheritdoc}
   */
  public function getIndex() {
    if ($this->index) {
      return $this->index;
    }

    $vid = $this->configFactory->get('forum.settings')->get('vocabulary');
    $index = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'tid' => 0,
      'container' => 1,
      'parents' => [],
      'isIndex' => TRUE,
      'vid' => $vid,
    ]);

    // Load the tree below.
    $index->forums = $this->getChildren($vid, 0);
    $this->index = $index;
    return $index;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    // Reset the index.
    $this->index = NULL;
    // Reset history.
    $this->history = [];
  }

  /**
   * {@inheritdoc}
   */
  public function checkNodeType(NodeInterface $node) {
    // Fetch information about the forum field.
    $field_definitions = $this->entityFieldManager->getFieldDefinitions('node', $node->bundle());
    return !empty($field_definitions['taxonomy_forums']);
  }

  /**
   * {@inheritdoc}
   */
  public function unreadTopics($term, $uid) {
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('forum', 'f', '[n].[vid] = [f].[vid] AND [f].[tid] = :tid', [':tid' => $term]);
    $query->leftJoin('history', 'h', '[n].[nid] = [h].[nid] AND [h].[uid] = :uid', [':uid' => $uid]);
    $query->addExpression('COUNT([n].[nid])', 'count');
    return $query
      ->condition('status', 1)
      // @todo This should be actually filtering on the desired node status
      //   field language and just fall back to the default language.
      ->condition('n.default_langcode', 1)
      ->condition('n.created', HISTORY_READ_LIMIT, '>')
      ->isNull('h.nid')
      ->addTag('node_access')
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = $this->defaultSleep();
    // Do not serialize static cache.
    unset($vars['history'], $vars['index'], $vars['lastPostData'], $vars['forumChildren'], $vars['forumStatistics']);
    return $vars;
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    $this->defaultWakeup();
    // Initialize static cache.
    $this->history = [];
    $this->lastPostData = [];
    $this->forumChildren = [];
    $this->forumStatistics = [];
    $this->index = NULL;
  }

}
