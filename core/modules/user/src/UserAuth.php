<?php

namespace Drupal\user;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordHashInterface;

/**
 * Validates user authentication credentials.
 */
class UserAuth implements UserAuthInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The password hashing service.
   *
   * @var \Drupal\Core\Password\PasswordHashInterface
   */
  protected $passwordChecker;

  /**
   * Constructs a UserAuth object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Password\PasswordHashInterface $password_checker
   *   The password service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PasswordHashInterface $password_checker) {
    $this->entityTypeManager = $entity_type_manager;
    $this->passwordChecker = $password_checker;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($username, #[\SensitiveParameter] $password) {
    $uid = FALSE;

    if (!empty($username) && strlen($password) > 0) {
      $account_search = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $username]);

      if ($account = reset($account_search)) {
        if ($this->passwordChecker->verify($password, $account->getPassword())) {
          // Successful authentication.
          $uid = $account->id();

          // Update user to new password scheme if needed.
          if ($this->passwordChecker->needsRehash($account->getPassword())) {
            $account->setPassword($password);
            $account->save();
          }
        }
      }
    }

    return $uid;
  }

}
