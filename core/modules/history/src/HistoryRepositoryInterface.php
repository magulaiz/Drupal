<?php

namespace Drupal\history;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface to store and retrieve the times of users latest activity with entities.
 */
interface HistoryRepositoryInterface {

  /**
   * Retrieves the times of a user's latest activity with an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user account.
   *
   * @return int|null
   *   A timestamp.
   */
  public function getTime(\Drupal\Core\Entity\EntityInterface $entity, ?AccountInterface $account): ?int;

  /**
   * Retrieves the times of a user's latest activity with entities.
   *
   * @param string $entity_type
   *   The entity type.
   * @param array $entity_ids
   *   The entity IDs.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user account.
   *
   * @return array
   *   Array of timestamps keyed by entity ID
   */
  public function getTimes(string $entity_type, array $entity_ids, ?AccountInterface $account): array;

  /**
   * Sets the time of a user's latest activity time with an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that history should be updated.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user account.
   * @param int|null $time
   *   (optional) The activity timestamp.
   *
   * @return self
   */
  public function setTime(EntityInterface $entity, ?AccountInterface $account, ?int $time): HistoryRepositoryInterface;

  /**
   * Sets the time of a user's latest activity time with an entity.
   *
   * @param string $entity_type 
   *   The entity type.
   * @param array $entity_ids
   *   The entity IDs.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user account.
   * @param int|null $time
   *   (optional) The activity timestamp.
   *
   * @return self
   */
  public function setTimes(string $entity_type, array $entity_ids, ?AccountInterface $account, ?int $time): HistoryRepositoryInterface;

  /**
   * Purges outdated history.
   *
   * @param int|null $time
   *   (optional) The timestamp before which history is outdated.
   */
  public function purge(?int $time): void;

  /**
   * Deletes the history for the given user account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to purge history.
   */
  public function deleteByUser(AccountInterface $account): void;

  /**
   * Deletes the history for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity that history should be deleted.
   */
  public function deleteByEntity(EntityInterface $entity): void;

  /**
   * Clears cached history.
   *
   * @param string|null $entity_type
   *   (optional)The entity type to remove cached history for.
   * @param array|null $entity_ids
   *   (optional) Entity IDs to remove from cache.
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   (optional) The user account to remove cached history for.
   *
   * @return self
   */
  public function resetCache(?string $entity_type, ?array $entity_ids, ?AccountInterface $account): HistoryRepositoryInterface;

}
