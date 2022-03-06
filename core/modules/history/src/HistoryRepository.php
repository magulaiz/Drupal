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
  public function getTime(EntityInterface $entity, AccountInterface $account = NULL, $default = NULL): ?int {
    $result = $this->getTimes($entity->getEntityTypeId(), [$entity->id()], $account, $default);
    return $result ? reset($result) : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimes(string $entity_type, array $entity_ids, AccountInterface $account = NULL, $default = NULL): array {
    if ($entity_type !== 'node') {
      throw new \InvalidArgumentException("History storage does not support entity types other than node.");
    }

    $account = $account ?? $this->currentUser;
    if ($account->isAnonymous()) {
      return [];
    }

    // Get times from cache where possible.
    $cached = $this->getCachedTimes($entity_type, $entity_ids, $account);
    $uncached_ids = array_diff($entity_ids, array_keys($cached));
    if (empty($uncached_ids)) {
      $result = $this->handleMissingTimes($entity_ids, $cached, $default);
      return $cached;
    }

    // Get uncached times from database.
    $queried = $this->connection->select('history', 'h')
      ->fields('h', ['nid', 'timestamp'])
      ->condition('uid', $account->id())
      ->condition('nid', $uncached_ids, 'IN')
      ->execute()
      ->fetchAllKeyed();
    $this->setCachedTimes($entity_type, $queried, $account);

    // Cache missing items with FALSE as time.
    $found = $cached + $queried;
    $missing_ids = array_diff($entity_ids, array_keys($found));
    $missing = array_fill_keys($missing_ids, FALSE);
    $this->setCachedTimes($entity_type, $missing, $account);

    $result = $this->handleMissingTimes($entity_ids, $found, $default);
    return $result;
  }

  /**
   * Handles entities for whom no history is present.
   *
   * @param array $entity_ids
   *   The entity ids that should be keys in the returned array.
   * @param array $times
   *   An array of times (or FALSE), keyed by entity id.
   * @param mixed $default
   *   (optional) A default value to use as time if none is given.
   *
   * @return array
   *   An array of times or default values, keyed by entity id.
   */
  protected function handleMissingTimes(array $entity_ids, array $times, $default = NULL) {
    // Allow 0 as a valid time, but filter out FALSE.
    $result = array_filter($times, 'strlen');
    // If default is specified, use it for entities without times.
    // Otherwise, exclude entities without times.
    if (!is_null($default)) {
      $missing = array_diff($entity_ids, array_keys($result));
      $defaultResult = array_fill_keys($missing, $default);
      $result = $result + $defaultResult;
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function setTime(EntityInterface $entity, AccountInterface $account = NULL, int $time = NULL): HistoryRepositoryInterface {
    $this->setTimes($entity->getEntityTypeId(), [$entity->id()], $account, $time);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setTimes($entity_type, $entity_ids, AccountInterface $account = NULL, int $time = NULL): HistoryRepositoryInterface {
    if ($entity_type !== 'node') {
      throw new \InvalidArgumentException("History storage does not support entity types other than node.");
    }

    $account = $account ?? $this->currentUser;
    if ($account->isAnonymous()) {
      return $this;
    }

    $time = $time ?? $this->time->getRequestTime();

    foreach ($entity_ids as $entity_id) {
      $this->connection->merge('history')
        ->keys([
          'uid' => $account->id(),
          'nid' => $entity_id,
        ])
        ->fields(['timestamp' => $time])
        ->execute();
      $this->setCachedTimes($entity_type, [$entity_id => $time], $account);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function purge(int $time = NULL): void {
    $time = $time ?? HISTORY_READ_LIMIT;
    $this->connection->delete('history')
      ->condition('timestamp', $time, '<')
      ->execute();
    $this->resetCache();
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
      ->condition('nid', $entity->id())
      ->execute();
    $this->resetCache($entity_type, [$entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(string $entity_type = NULL, array $entity_ids = NULL, AccountInterface $account = NULL): HistoryRepositoryInterface {
    $account_ids = $account ? [$account->id()] : array_keys(static::$cache);
    foreach ($account_ids as $account_id) {
      if (empty($entity_type) && empty($entity_ids)) {
        unset(static::$cache[$account_id]);
        continue;
      }
      $entity_types = $entity_type ? [$entity_type] : array_keys(static::$cache[$account_id]);
      foreach ($entity_types as $entity_type) {
        if (empty($entity_ids)) {
          unset(static::$cache[$account_id][$entity_type]);
          continue;
        }
        foreach ($entity_ids as $entity_id) {
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
   * @param array $times
   *   An array of timestamps keyed by entity ID.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   */
  protected function setCachedTimes(string $entity_type, array $times, AccountInterface $account) {
    static::$cache[$account->id()][$entity_type] = $times + (static::$cache[$account->id()][$entity_type] ?? []);
  }

}
