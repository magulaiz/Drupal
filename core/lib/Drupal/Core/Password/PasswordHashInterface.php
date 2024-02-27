<?php

namespace Drupal\Core\Password;

/**
 * Secure password hashing functions for user authentication.
 */
interface PasswordHashInterface extends PasswordInterface {

  /**
   * Verify whether a plain text password matches a hashed password.
   *
   * @param string $password
   *   A plain-text password
   * @param string $hash
   *   A hashed password.
   *
   * @return bool
   *   TRUE if the password is valid, FALSE if not.
   */
  public function verify(#[\SensitiveParameter] $password, #[\SensitiveParameter] $hash);

}
