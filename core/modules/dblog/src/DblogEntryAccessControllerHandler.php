<?php

namespace Drupal\dblog;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the dblog entry entity type.
 *
 * @see \Drupal\dblog\Entity\DblogEntry
 */
class DblogEntryAccessControllerHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access site reports');
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = [], $return_as_object = FALSE) {
    $result = AccessResult::neutral();
    return $return_as_object ? $result : $result->isAllowed();
  }

}
