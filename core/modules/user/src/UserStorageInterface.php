<?php

namespace Drupal\user;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an interface for user entity storage classes.
 *
 * @method \Drupal\user\UserInterface create(array $values = [])
 * @method null|\Drupal\user\UserInterface load($id)
 * @method null|\Drupal\user\UserInterface loadRevision($revision_id)
 * @method null|\Drupal\user\UserInterface loadUnchanged($id)
 * @method \Drupal\user\UserInterface[] loadMultiple(array $ids = NULL)
 * @method \Drupal\user\UserInterface[] loadByProperties(array $values = [])
 * @method null|int save(\Drupal\user\UserInterface $entity)
 * @method void restore(\Drupal\user\UserInterface $entity)
 */
interface UserStorageInterface extends ContentEntityStorageInterface {

  /**
   * Update the last login timestamp of the user.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account.
   */
  public function updateLastLoginTimestamp(UserInterface $account);

  /**
   * Update the last access timestamp of the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user object.
   * @param int $timestamp
   *   The last access timestamp.
   */
  public function updateLastAccessTimestamp(AccountInterface $account, $timestamp);

  /**
   * Delete role references.
   *
   * @param array $rids
   *   The list of role IDs being deleted. The storage should
   *   remove permission and user references to this role.
   */
  public function deleteRoleReferences(array $rids);

}
