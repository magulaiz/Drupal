<?php

namespace Drupal\user;

use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface for 'user.timestamp' service.
 */
interface UserTimestampInterface {

  /**
   * Updates the current user's last access time.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account object.
   * @param int|null $timestamp
   *   (optional) The timestamp to be set as last access time. If omitted, it
   *   defaults to the request time.
   */
  public function setLastAccessTime(AccountInterface $account, ?int $timestamp = NULL): void;

  /**
   * Returns the last access time for a given user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account object.
   *
   * @return int
   *   The last access time as a timestamp.
   */
  public function getLastAccessTime(AccountInterface $account): int;

  /**
   * Update the last login timestamp of the user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account object.
   * @param int|null $timestamp
   *   (optional) The timestamp to be set as last login time. If omitted, it
   *   defaults to the request time.
   */
  public function setLastLoginTime(UserInterface $account, ?int $timestamp = NULL): void;

  /**
   * Returns the last login time for a given user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account object.
   *
   * @return int
   *   The last login time as a timestamp.
   */
  public function getLastLoginTime(UserInterface $account): int;

}
