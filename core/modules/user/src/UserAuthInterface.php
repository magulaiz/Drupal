<?php

namespace Drupal\user;

@trigger_error('The ' . __NAMESPACE__ . '\UserAuthInterface is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\user\UserAuthenticationInterface instead. See https://www.drupal.org/node/3411040', E_USER_DEPRECATED);

/**
 * An interface for validating user authentication credentials.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Implement
 * Drupal\user\UserAuthenticationInterface instead.
 * @see https://www.drupal.org/node/3411040
 */
interface UserAuthInterface {

  /**
   * Validates user authentication credentials.
   *
   * @param string $username
   *   The user name to authenticate.
   * @param string $password
   *   A plain-text password, such as trimmed text from form values.
   *
   * @return int|bool
   *   The user's uid on success, or FALSE on failure to authenticate.
   */
  public function authenticate($username, #[\SensitiveParameter] $password);

}
