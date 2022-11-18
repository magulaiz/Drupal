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
   * @param \Drupal\Core\Password\PasswordInterface $legacyPassword
   *   The legacy password hashing service.
   */
  public function __construct(
    protected int $cost,
    protected PasswordInterface $legacyPassword
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
    // Drupal >= 10.1.x hashed password.
    if (str_starts_with($hash, '$2y$')) {
      $stored_hash = $hash;
    }
    // Drupal 6 (or any md5) hashed password migrated to Drupal >= 10.1.x.
    elseif (str_starts_with($hash, 'U$2y$')) {
      $stored_hash = substr($hash, 1);
      $password = md5($password);
    }
    // Possible legacy hash. This may be:
    // - Either a Drupal 7, < 10.1.0 hash,
    // - Or a Drupal 6 (or md5) hash migrated to Drupal < 10.1.0.
    else {
      return $this->legacyPassword->check($password, $hash);
    }

    return password_verify($password, $stored_hash);
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
