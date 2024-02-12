<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\comment\CommentInterface;
use Drupal\comment\CommentManagerInterface;
use Drupal\comment\CommentStorageInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore fieldcompare

/**
 * The MongoDB implementation of \Drupal\comment\CommentStorage.
 */
class CommentStorage extends ContentEntityStorage implements CommentStorageInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a CommentStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_info
   *   An array of entity info for the entity type.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeInterface $entity_info, Connection $database, EntityFieldManagerInterface $entity_field_manager, AccountInterface $current_user, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, MemoryCacheInterface $memory_cache, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($entity_info, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_info) {
    return new static(
      $entity_info,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('current_user'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxThread(CommentInterface $comment) {
    $result = $this->database->select('comment', 'c')
      ->fields('c', ['comment_translations'])
      ->condition('comment_translations.entity_id', (int) $comment->getCommentedEntityId())
      ->condition('comment_translations.field_name', $comment->getFieldName())
      ->condition('comment_translations.entity_type', $comment->getCommentedEntityTypeId())
      ->condition('comment_translations.default_langcode', TRUE)
      ->execute()
      ->fetchAll();

    $max_thread = '';
    foreach ($result as $row) {
      foreach ($row->comment_translations as $comment_translation) {
        if (($comment_translation['entity_id'] == $comment->getCommentedEntityId()) &&
          ($comment_translation['entity_type'] == $comment->getCommentedEntityTypeId()) &&
          ($comment_translation['field_name'] == $comment->getFieldName()) &&
          ($comment_translation['default_langcode'] == CommentInterface::PUBLISHED)
        ) {
          if ($comment_translation['thread'] > $max_thread) {
            $max_thread = $comment_translation['thread'];
          }
        }
      }
    }
    return $max_thread;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxThreadPerThread(CommentInterface $comment) {
    $result = $this->database->select('comment', 'c')
      ->fields('c', ['comment_translations'])
      ->condition('comment_translations.entity_id', (int) $comment->getCommentedEntityId())
      ->condition('comment_translations.field_name', $comment->getFieldName())
      ->condition('comment_translations.entity_type', $comment->getCommentedEntityTypeId())
      ->condition('comment_translations.thread', $comment->getParentComment()->getThread() . '.%', 'LIKE')
      ->condition('comment_translations.default_langcode', TRUE)
      ->execute()
      ->fetchAll();

    $max_thread = '';
    foreach ($result as $row) {
      foreach ($row->comment_translations as $comment_translation) {
        if (($comment_translation['entity_id'] == $comment->getCommentedEntityId()) &&
          ($comment_translation['entity_type'] == $comment->getCommentedEntityTypeId()) &&
          ($comment_translation['field_name'] == $comment->getFieldName()) &&
          ($comment_translation['default_langcode'] == CommentInterface::PUBLISHED)
        ) {
          $pattern = '/^' . $comment->getParentComment()->getThread() . '.*/';
          if (($comment_translation['thread'] > $max_thread) && preg_match($pattern, $comment_translation['thread'])) {
            $max_thread = $comment_translation['thread'];
          }
        }
      }
    }
    return $max_thread;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayOrdinal(CommentInterface $comment, $comment_mode, $divisor = 1) {
    // Count how many comments (c1) are before $comment (c2) in display order.
    // This is the 0-based display ordinal.
    $query = $this->database->select('comment', 'c1')
      ->fields('c1', ['cid']);

    // The comment_translations field must be added in a special way, because
    // the join operation will overwrite its value.
    $query->addPreJoinField('c1_comment_translations', 'comment_translations');

    $join_extra = [
      [
        'field' => 'comment_translations.entity_type',
        'left_field' => 'comment_translations.entity_type',
      ],
      [
        'field' => 'comment_translations.field_name',
        'left_field' => 'comment_translations.field_name',
      ]
    ];
    $query->addMongodbJoin('INNER', 'comment', 'comment_translations.entity_id', ['table' => 'comment', 'alias' => 'c1'], 'comment_translations.entity_id', '=', 'c2', $join_extra);

    $query->condition('c2.comment_translations.cid', (int) $comment->id());
    if (!$this->currentUser->hasPermission('administer comments')) {
      $query->condition('c1_comment_translations.status', (bool) CommentInterface::PUBLISHED);
    }

    if ($comment_mode == CommentManagerInterface::COMMENT_MODE_FLAT) {
      // For rendering flat comments, cid is used for ordering comments due to
      // unpredictable behavior with timestamp, so we make the same assumption
      // here.
      $query->condition('c1_comment_translations.cid', (int) $comment->id(), '<');
    }
    else {
      // For threaded comments, the c.thread column is used for ordering. We can
      // use the sorting code for comparison, but must remove the trailing
      // slash.
      $query->addSubstringField('c1_thread', 'c1_comment_translations.thread', 1, -2);

      // The array "c2.comment_translations" is unwound and yet the MongoDB
      // throws an exception that it is an array and not a string. For MongoDB
      // it would be better to store the value thread as a string with a
      // trailing slash and as an integer value.
      $query->addSubstringField('c2_thread', 'c2.comment_translations.thread', 1, -2);
      $query->condition('c2_thread', ['field' => 'c1_thread', 'operator' => '>'], 'FIELDCOMPARE');
    }

    $query->condition('c1_comment_translations.default_langcode', TRUE);
    $query->condition('c2.comment_translations.default_langcode', TRUE);

    $result = $query->execute()->fetchAll();
    $ordinal = count($result);
    return ($divisor > 1) ? floor($ordinal / $divisor) : $ordinal;
  }

  /**
   * {@inheritdoc}
   */
  public function getNewCommentPageNumber($total_comments, $new_comments, FieldableEntityInterface $entity, $field_name) {
    $field = $entity->getFieldDefinition($field_name);
    $comments_per_page = $field->getSetting('per_page');

    if ($total_comments <= $comments_per_page) {
      // Only one page of comments.
      $count = 0;
    }
    elseif ($field->getSetting('default_mode') == CommentManagerInterface::COMMENT_MODE_FLAT) {
      // Flat comments.
      $count = $total_comments - $new_comments;
    }
    else {
      // Threaded comments.

      // 1. Find all the threads with a new comment.
      $unread_threads = $this->database->select('comment', 'comment')
        ->fields('comment', ['thread'])
        ->condition('comment_translations.entity_id', (int) $entity->id())
        ->condition('comment_translations.entity_type', $entity->getEntityTypeId())
        ->condition('comment_translations.field_name', $field_name)
        ->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED)
        ->condition('comment_translations.default_langcode', TRUE)
        ->orderBy('comment_translations.created', 'DESC')
        ->orderBy('comment_translations.cid', 'DESC')
        ->range(0, $new_comments)
        ->execute()
        ->fetchCol();

      // 2. Find the first thread.
      foreach ($unread_threads as &$unread_thread) {
        $unread_thread = substr($unread_thread, 0, -1);
        $unread_thread = ltrim($unread_thread, '0');
      }
      natsort($unread_threads);

      $first_thread = reset($unread_threads);

      // 3. Find the number of the first comment of the first unread thread.
      $threads_query = $this->database->select('comment', 'comment')
        ->fields('comment', ['cid'])
        ->condition('comment_translations.entity_id', (int) $entity->id())
        ->condition('comment_translations.entity_type', $entity->getEntityTypeId())
        ->condition('comment_translations.field_name', $field_name)
        ->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED);
      $threads_query->addSubstringField('thread_without_slash', 'thread', 1, -2);
      $threads_query->condition('thread_without_slash', $first_thread, '<');
      $cids = $threads_query->execute()->fetchAll();
      $count = count($cids);
    }

    return $comments_per_page > 0 ? (int) ($count / $comments_per_page) : 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getChildCids(array $comments) {
    $cids = [];
    foreach (array_keys($comments) as $cid) {
      $cids[] = (int) $cid;
    }
    return $this->database->select('comment', 'c')
      ->fields('c', ['cid'])
      ->condition('comment_translations.pid', $cids, 'IN')
      ->condition('comment_translations.default_langcode', TRUE)
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function loadThread(EntityInterface $entity, $field_name, $mode, $comments_per_page = 0, $pager_id = 0) {
    $query = $this->database->select('comment', 'c');
    $query->addField('c', 'cid');
    $query
      ->condition('comment_translations.entity_id', (int) $entity->id())
      ->condition('comment_translations.entity_type', $entity->getEntityTypeId())
      ->condition('comment_translations.field_name', $field_name)
      ->condition('comment_translations.default_langcode', TRUE)
      ->addTag('entity_access')
      ->addTag('comment_filter')
      ->addMetaData('base_table', 'comment')
      ->addMetaData('entity', $entity)
      ->addMetaData('field_name', $field_name);

    if ($comments_per_page) {
      $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
        ->limit($comments_per_page);
      if ($pager_id) {
        $query->element($pager_id);
      }

//      $query->setCountQueryMethod($this, 'countQueryThread', [$entity, $field_name, $comments_per_page]);
    }

    if (!$this->currentUser->hasPermission('administer comments')) {
      $query->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED);
    }
    if ($mode == CommentManagerInterface::COMMENT_MODE_FLAT) {
      $query->orderBy('cid', 'ASC');
    }
    else {
      // See comment above. Analysis reveals that this doesn't cost too
      // much. It scales much much better than having the whole comment
      // structure.
      $query->addSubstringField('torder', 'comment_translations.thread', 1, -2);
      $query->orderBy('torder', 'ASC');
    }
    $cids = $query->execute()->fetchCol();

    $comments = [];
    if ($cids) {
      $comments = $this->loadMultiple($cids);
    }

    return $comments;
  }

  /**
   * {@inheritdoc}
   */
  public function countQueryThread($entity, $field_name, $comments_per_page = 0) {
    $query = $this->database->select('comment', 'c')
      ->condition('comment_translations.entity_id', (int) $entity->id())
      ->condition('comment_translations.entity_type', $entity->getEntityTypeId())
      ->condition('comment_translations.field_name', $field_name)
      ->condition('comment_translations.default_langcode', TRUE);
    if ($comments_per_page) {
      $query->condition('comment_translations.status', (bool) CommentInterface::PUBLISHED);
    }
    $result = $query->addTag('entity_access')
      ->addTag('comment_filter')
      ->addMetaData('base_table', 'comment')
      ->addMetaData('entity', $entity)
      ->addMetaData('field_name', $field_name)
      ->execute()
      ->fetchAll();

    $count = 0;
    foreach ($result as $comment) {
      foreach ($comment->comment_translations as $comment_translation) {
        if (($comment_translation['entity_id'] == $entity->id()) &&
          ($comment_translation['entity_type'] == $entity->getEntityTypeId()) &&
          ($comment_translation['field_name'] == $field_name) &&
          ($comment_translation['default_langcode'])) {

          if (!$this->currentUser->hasPermission('administer comments') && $comments_per_page) {
            if ($comment_translation['status'] == CommentInterface::PUBLISHED) {
              $count++;
            }
          }
          else {
            $count++;
          }
        }
      }
    }

    return $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnapprovedCount() {
    return $this->database->select('comment', 'c')
      ->condition('comment_translations.status', (bool) CommentInterface::NOT_PUBLISHED)
      ->condition('comment_translations.default_langcode', TRUE)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

}
