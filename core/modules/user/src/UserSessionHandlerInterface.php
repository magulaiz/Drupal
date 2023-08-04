<?php

declare(strict_types=1);

namespace Drupal\user;

/**
 * Handler for user sessions.
 */
interface UserSessionHandlerInterface {

  /**
   * Login the current user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user.
   */
  public function login(UserInterface $user): void;

  /**
   * Log out the current user.
   */
  public function logout(): void;

}
