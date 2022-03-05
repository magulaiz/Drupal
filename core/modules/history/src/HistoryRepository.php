<?php

namespace Drupal\history;

use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Service class to store and retrieve the times of users latest activity with entities.
 */
class HistoryRepository implements HistoryRepositoryInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The cache.
   *
   * @var array
   */
  protected static $cache = [];

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs the history repository.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(Connection $connection, TimeInterface $time, AccountInterface $current_user) {
    $this->connection = $connection;
    $this->time = $time;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getTime(EntityInterface $entity, ?AccountInterface $account): ?int {
    $result = $this->getTimes($entity->getEntityTypeId(), [$entity->id()], $account);
    return $result ? reset($result) : NULL; 
  }

  /**
   * {@inheritdoc}
   */
  public function getTimes(string $entity_type, array $entity_ids, ?AccountInterface $account): array {
    if ($entity_type !== 'node') {
      throw new \InvalidArgumentException("History storage does not support entity types other than node.");
    }

    $account = $account ?? $this->currentUser;
    if ($account->isAnonymous()) {
      return [];
    }

    $cached = $this->getCachedTimes($entity_type, $entity_ids, $account);
    $uncached = array_diff($entity_ids, array_keys($cached));
    if (empty($uncached)) {
      return $cached;
    }

    $queried = $this->connection->select('history', 'h')
      ->fields('h', ['nid', 'timestamp'])
      ->condition('uid', $account->id())
      ->condition('nid', $uncached, 'IN')
      ->execute()
      ->fetchAllKeyed();
    $this->setCache($entity_type, $queried, $account);

    return $cached + $queried;
  }

  /**
   * {@inheritdoc}
   */
  public function setTime(EntityInterface $entity, ?AccountInterface $account, ?int $time): HistoryRepositoryInterface {
    $this->setTimes($entity->getEntityTypeId(), [$entity->id()], $account, $time);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimes($entity_type, $entity_ids, ?AccountInterface $account, ?int $time): HistoryRepositoryInterface {
    if ($entity_type !== 'node') {
      throw new \InvalidArgumentException("History storage does not support entity types other than node.");
    }

    $account = $account ?? $this->currentUser;
    if ($account->isAnonymous()) {
      return $this;
    }

    $time = $time ?? $this->time->getRequestTime();

    foreach($entity_ids as $entity_id) {
      $this->connection->merge('history')
        ->keys([
          'uid' => $account->id(),
          'nid' => $entity_id,
        ])
        ->fields(['timestamp' => $time])
        ->execute();
      $this->setCachedTime($entity_type, $entity_id, $account, $time);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function purge(?int $time): void {
    $time = $time ?? HISTORY_READ_LIMIT;
    $this->connection->delete('history')
      ->condition('timestamp', $time, '<')
      ->execute();
    $this->clearCache();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByUser(AccountInterface $account): void {
    $this->connection->delete('history')
      ->condition('uid', $account->id())
      ->execute();
      $this->resetCache(NULL, NULL, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByEntity(EntityInterface $entity): void {
    $entity_type = $entity->getEntityTypeId();
    if ($entity_type !== 'node') {
      throw new \InvalidArgumentException("History storage does not support entity types other than node.");
    }
    $this->connection->delete('history')
      ->condition('entity_id', $entity->id())
      ->execute();
    $this->resetCache($entity_type, [$entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(?string $entity_type, ?array $entity_ids, ?AccountInterface $account): HistoryRepositoryInterface {
    $account_ids = $account ? [$account->id()] : array_keys(static::$cache);
    foreach($account_ids as $account_id) {
      if (empty($entity_type) && empty($entity_ids)) {
        unset(static::$cache[$account_id]);
        continue;
      }
      $entity_types = $entity_type ? [$entity_type] : array_keys(static::$cache[$account_id]);
      foreach($entity_types as $entity_type) {
          if (empty($entity_ids)) {
            unset(static::$cache[$account_id][$entity_type]);
            continue;
          }
          foreach($entity_ids as $entity_id) {
            unset(static::$cache[$account_id][$entity_type][$entity_id]);
          }
        }
    }
    return $this;
  }

  /**
   * Retrieves the cached times of a user's latest activity with entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $entity_ids
   *   The entity IDs.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return array
   *   Array of timestamps keyed by entity ID
   */
  protected function getCachedTimes(string $entity_type, array $entity_ids, AccountInterface $account): array {
    return array_intersect_key(static::$cache[$account->id()][$entity_type] ?? [], array_flip($entity_ids));
  }

  /**
   * Sets the cached times of a user's latest activity with entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param int $entity_id
   *   The entity IDs.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param int $time
   *   The activity timestamp.
   */
  protected function setCachedTime(string $entity_type, int $entity_id, AccountInterface $account, int $time): array {
    static::$cache[$account->id()][$entity_type][$entity_id] = $time;
  }

}
