<?php

namespace Drupal\user;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;

/**
 * Default implementation of 'user.timestamp' service.
 */
class UserTimestamp implements UserTimestampInterface {

  /**
   * Constructs a new service instance.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   *   The key/value factory service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The date/time service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings.
   */
  public function __construct(
    protected KeyValueFactoryInterface $keyValue,
    protected TimeInterface $time,
    protected Settings $settings,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAccessTime(AccountInterface $account, ?int $timestamp = NULL): void {
    if ($account->isAuthenticated()) {
      $timestamp ??= $this->time->getRequestTime();
      if ($timestamp - $account->getLastAccessedTime() > $this->settings->get('session_write_interval', 180)) {
        // Do that no more than once per 180 seconds.
        $this->keyValue->get('user.timestamp.access')->set($account->id(), $timestamp);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAccessTime(AccountInterface $account): int {
    return $this->keyValue->get('user.timestamp.access')->get($account->id(), 0);
  }

  /**
   * {@inheritdoc}
   */
  public function setLastLoginTime(UserInterface $account, ?int $timestamp = NULL): void {
    $timestamp ??= $this->time->getRequestTime();
    $account->setLastLoginTime($timestamp);
    $this->keyValue->get('user.timestamp.login')->set($account->id(), $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastLoginTime(UserInterface $account): int {
    return $this->keyValue->get('user.timestamp.login')->get($account->id(), 0);
  }

}
