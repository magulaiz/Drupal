<?php

namespace Drupal\user;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Reusable code for user timestamp field item list classes.
 *
 * @see \Drupal\user\UserLastAccessFieldItemList
 * @see \Drupal\user\UserLastLoginFieldItemList
 */
trait UserTimestampFieldItemListTrait {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue(): void {
    /** @var \Drupal\Core\Session\AccountInterface $account */
    $account = $this->getEntity();
    if (!$account->isAnonymous()) {
      $this->list[0] = $this->createItem(0, $this->getTimestamp($account));
    }
  }

  /**
   * Returns the required timestamp.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account object.
   *
   * @return int
   *   The timestamp.
   */
  abstract protected function getTimestamp(AccountInterface $account): int;

}
