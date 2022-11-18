<?php

namespace Drupal\Core\Password;

/**
 * Secure password hashing functions for user authentication.
 */
interface PasswordInterface {

  /**
   * Maximum password length.
   */
  const PASSWORD_MAX_LENGTH = 512;

  /**
   * Hash a password using a secure hash.
   *
   * @param string $password
   *   A plain-text password.
   *
   * @return string
   *   A string containing the hashed password, or FALSE on failure.
   */
  public function hash(#[\SensitiveParameter] $password);

  /**
   * Check whether a plain text password matches a hashed password.
   *
   * @param string $password
   *   A plain-text password
   * @param string $hash
   *   A hashed password.
   *
   * @return bool
   *   TRUE if the password is valid, FALSE if not.
   */
  public function check(#[\SensitiveParameter] $password, #[\SensitiveParameter] $hash);

  /**
   * Check whether a hashed password needs to be replaced with a new hash.
   *
   * This is typically called during the login process in order to trigger the
   * rehashing of the password, as in that stage, the plain text password is
   * available.
   *
   * This method returns TRUE in one of the next cases:
   * - The parameters of hashing service were changed. For instance, the hashing
   *   cost parameter 'password_hash_cost' was changed in core.services.yml.
   * - The password hash was hashed in Drupal < 10.1.0.
   * - The hash was migrated from a system that uses MD5 hashes, like Drupal 6,
   *   or from Drupal 7.
   *
   * @param string $hash
   *   The hash to be checked.
   *
   * @return bool
   *   TRUE if the hash is outdated and needs rehash.
   */
  public function needsRehash($hash);

}
