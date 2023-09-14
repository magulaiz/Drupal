<?php

namespace Drupal\user;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordInterface;

/**
 * Validates user authentication credentials.
 */
class UserAuth implements UserAuthInterface {

  /**
   * Constructs a UserAuth object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Password\PasswordInterface $passwordChecker
   *   The password service.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager, protected PasswordInterface $passwordChecker)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($username, #[\SensitiveParameter] $password) {
    $uid = FALSE;

    if (!empty($username) && strlen($password) > 0) {
      $account_search = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $username]);

      if ($account = reset($account_search)) {
        if ($this->passwordChecker->check($password, $account->getPassword())) {
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
