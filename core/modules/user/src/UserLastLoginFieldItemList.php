<?php

namespace Drupal\user;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Session\AccountInterface;

/**
 * Field item list class for the user 'login' computed field.
 */
class UserLastLoginFieldItemList extends FieldItemList {

  use UserTimestampFieldItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function getTimestamp(AccountInterface $account): int {
    /** @var \Drupal\user\UserTimestampInterface $user_timestamp */
    $user_timestamp = \Drupal::service('user.timestamp');
    return $user_timestamp->getLastLoginTime($account);
  }

}
