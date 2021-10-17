<?php

namespace Drupal\quickedit\Access;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for in-place editing entity fields.
 */
interface QuickEditEntityFieldAccessCheckInterface {

  /**
   * Checks access to edit the requested field of the requested entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function accessEditEntityField(EntityInterface $entity, $field_name, AccountInterface $account = NULL);

}
