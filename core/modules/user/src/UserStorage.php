<?php

namespace Drupal\user;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;

/**
 * Controller class for users.
 *
 * This extends the Drupal\Core\Entity\Sql\SqlContentEntityStorage class,
 * adding required special handling for user objects.
 */
class UserStorage extends SqlContentEntityStorage implements UserStorageInterface {

  /**
   * {@inheritdoc}
   */
  protected function doSaveFieldItems(ContentEntityInterface $entity, array $names = []) {
    // The anonymous user account is saved with the fixed user ID of 0. MySQL
    // does not support inserting an ID of 0 into serial field unless the SQL
    // mode is set to NO_AUTO_VALUE_ON_ZERO.
    // @todo https://drupal.org/i/3222123 implement a generic fix for all entity
    //   types.
    if ($entity->id() === 0) {
      $database = \Drupal::database();
      if ($database->databaseType() === 'mysql') {
        $sql_mode = $database->query("SELECT @@sql_mode;")->fetchField();
        $database->query("SET sql_mode = '$sql_mode,NO_AUTO_VALUE_ON_ZERO'");
      }
    }
    parent::doSaveFieldItems($entity, $names);

    // Reset the SQL mode if we've changed it.
    if (isset($sql_mode, $database)) {
      $database->query("SET sql_mode = '$sql_mode'");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateLastLoginTimestamp(UserInterface $account) {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Instead, use the 'user.timestamp' service with the ::setLastLoginTime() method. See https://www.drupal.org/node/3300476", E_USER_DEPRECATED);
    \Drupal::service('user.timestamp')->setLastLoginTime($account);
  }

  /**
   * {@inheritdoc}
   */
  public function updateLastAccessTimestamp(AccountInterface $account, $timestamp) {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Instead, use the 'user.timestamp' service with the ::setLastAccessTime() method. See https://www.drupal.org/node/3300476", E_USER_DEPRECATED);
    \Drupal::service('user.timestamp')->setLastAccessTime($account);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRoleReferences(array $rids) {
    // Remove the role from all users.
    $this->database->delete('user__roles')
      ->condition('roles_target_id', $rids)
      ->execute();

    $this->resetCache();
  }

}
