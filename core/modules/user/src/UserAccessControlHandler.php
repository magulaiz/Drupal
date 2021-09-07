<?php

namespace Drupal\user;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the user entity type.
 *
 * @see \Drupal\user\Entity\User
 */
class UserAccessControlHandler extends EntityAccessControlHandler {

  /**
   * Allow access to user label.
   *
   * @var bool
   */
  protected $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\user\UserInterface $entity*/

    // The anonymous user's username can be viewed always.
    if ($operation === 'view label') {
      return $this->canViewUserName($account, $entity);
    }

    // The anonymous user's profile can neither be viewed, updated nor deleted.
    if ($entity->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // Administrators can view/update/delete all user profiles.
    if ($account->hasPermission('administer users')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
        // Only allow view access if the account is active.
        if ($account->hasPermission('access user profiles') && $entity->isActive()) {
          return AccessResult::allowed()->cachePerPermissions()->addCacheableDependency($entity);
        }
        // Users can view own profiles at all times.
        elseif ($account->id() == $entity->id()) {
          return AccessResult::allowed()->cachePerUser();
        }
        else {
          return AccessResultNeutral::neutral("The 'access user profiles' permission is required and the user must be active.")->cachePerPermissions()->addCacheableDependency($entity);
        }
        break;

      case 'update':
        // Users can always edit their own account.
        $access_result = AccessResult::allowedIf($account->id() == $entity->id())->cachePerUser();
        if (!$access_result->isAllowed() && $access_result instanceof AccessResultReasonInterface) {
          $access_result->setReason("Users can only update their own account, unless they have the 'administer users' permission.");
        }
        return $access_result;

      case 'delete':
        // Users with 'cancel account' permission can cancel their own account.
        return AccessResult::allowedIfHasPermission($account, 'cancel account')
          ->andIf(AccessResult::allowedIf($account->id() == $entity->id())->cachePerUser());
    }

    // No opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    // Fields that are not implicitly allowed to administrative users.
    $explicit_check_fields = [
      'pass',
    ];

    // Administrative users are allowed to edit and view all fields.
    if (!in_array($field_definition->getName(), $explicit_check_fields) && $account->hasPermission('administer users')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    // Flag to indicate if this user entity is the own user account.
    /** @var \Drupal\user\UserInterface|null $other */
    $other = NULL;
    $is_own_account = FALSE;
    // A stub user is not a valid user.
    if ($items && $items->getEntity()->id() !== NULL) {
      $other = $items->getEntity();
      $is_own_account = $other->id() == $account->id();
    }
    switch ($field_definition->getName()) {
      case 'name':
        if ($other) {
          if ($operation == 'view') {
            return $this->canViewUserName($account, $other);
          }

          if ($operation == 'edit') {
            // Allow edit access for the own username if the permission is
            // satisfied.
            if ($is_own_account && $account->hasPermission('change own username')) {
              return AccessResult::allowed()->cachePerPermissions()->cachePerUser();
            }
          }
        }
        // BC layer which probably should be removed later, if the other entity
        // is not defined, let's return allowed.
        if ($other === NULL && in_array($operation, ['view', 'edit'], TRUE)) {
          return AccessResult::allowed()->cachePerPermissions();
        }

        return AccessResult::neutral();

      case 'mail':
        // Only check for the 'view user email addresses' permission and a view
        // operation. Use case fall-through for all other cases.
        if ($operation == 'view' && $account->hasPermission('view user email addresses')) {
          return AccessResult::allowed()->cachePerPermissions();
        }
      case 'preferred_langcode':
      case 'preferred_admin_langcode':
      case 'timezone':
        // Allow view access to own mail address and other personalization
        // settings.
        if ($operation == 'view') {
          return AccessResult::allowedIf($is_own_account)->cachePerUser();
        }
        // Anyone that can edit the user can also edit this field.
        return AccessResult::allowed()->cachePerPermissions();

      case 'pass':
        // Allow editing the password, but not viewing it.
        return ($operation == 'edit') ? AccessResult::allowed() : AccessResult::forbidden();

      case 'created':
        // Allow viewing the created date, but not editing it.
        return ($operation == 'view') ? AccessResult::allowed() : AccessResult::neutral();

      case 'roles':
      case 'status':
      case 'access':
      case 'login':
      case 'init':
        return AccessResult::neutral();
    }

    return parent::checkFieldAccess($operation, $field_definition, $account, $items);
  }

  /**
   * Checks if the current user can view a username.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\user\UserInterface $other
   *   The other user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   An access result.
   */
  final protected function canViewUserName(AccountInterface $current_user, UserInterface $other) {
    // Username of anonymous is always visible to everyone.
    if ($other->isAnonymous()) {
      return AccessResult::allowed()->setCacheMaxAge(CacheBackendInterface::CACHE_PERMANENT);
    }

    // Users with this permission can always access.
    if ($current_user->hasPermission('administer users')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    // Users can always see their own usernames, but we must avoid having
    // a result that varies per user.
    if ($other->id() == $current_user->id()) {
      return AccessResult::allowed()->cachePerUser()->setCacheMaxAge(CacheBackendInterface::CACHE_PERMANENT);
    }

    // Users with this permission can always see other user's username.
    if ($current_user->hasPermission('view usernames')) {
      return AccessResult::allowed()->addCacheableDependency($other)->cachePerPermissions();
    }

    // No opinion but the above used cache dependencies must be applied on this
    // to ensure proper cache invalidation.
    return AccessResult::neutral()->addCacheableDependency($other)->cachePerPermissions();
  }

}
