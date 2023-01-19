<?php

namespace Drupal\Core\Password;

/**
 * Secure password hashing functions based on PHP >= 5.5.0 password hashing.
 *
 * @see http://php.net/manual/en/book.password.php
 */
class PhpPassword implements PasswordInterface {

  /**
   * Constructs a new password hashing instance.
   *
   * @param int $cost
   *   The algorithmic cost that should be used. This is the same 'cost'
   *   option as is used by the password_hash() function.
   */
  public function __construct(
    protected int $cost
  ) {}

  /**
   * {@inheritdoc}
   */
  public function hash($password) {
    // Prevent DoS attacks by refusing to hash large passwords.
    if (strlen($password) > static::PASSWORD_MAX_LENGTH) {
      return FALSE;
    }

    return password_hash($password, PASSWORD_BCRYPT, $this->getOptions());
  }

  /**
   * {@inheritdoc}
   */
  public function check($password, $hash) {
    // Prevent DoS attacks by refusing to hash large passwords.
    if (strlen($password) > static::PASSWORD_MAX_LENGTH) {
      return FALSE;
    }

    return password_verify($password, $hash);
  }

  /**
   * {@inheritdoc}
   */
  public function needsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_BCRYPT, $this->getOptions());
  }

  /**
   * Returns password options.
   *
   * @return array
   *   Associative array with password options.
   */
  protected function getOptions() {
    return ['cost' => $this->cost];
  }

}
