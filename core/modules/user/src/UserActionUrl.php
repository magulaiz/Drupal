<?php

namespace Drupal\user;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\user\Plugin\views\argument_default\CurrentUser;

class UserActionUrl {

  /**
   * The current user.
   *
   * @var \Drupal\user\Plugin\views\argument_default\CurrentUser
   */
  protected $currentUser;

  /**
   * UserActionUrl constructor.
   *
   * @param \Drupal\user\Plugin\views\argument_default\CurrentUser $currentUser
   */
  public function __construct(CurrentUser $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * Get a user action url using the related route.
   *
   * @param string $route
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param array $options
   *
   * @return \Drupal\Core\Url
   */
  public function fromRoute(string $route, ?UserInterface $account = NULL, array $options = []): Url {
    $account = $account ?: $this->currentUser;
    $timestamp = \Drupal::time()->getRequestTime();
    $langcode = isset($options['langcode']) ? $options['langcode'] : $account->getPreferredLangcode();

    $url_options = [
      'absolute' => TRUE,
      'language' => \Drupal::languageManager()->getLanguage($langcode),
    ];

    return Url::fromRoute($route, [
      'user' => $account->id(),
      'timestamp' => $timestamp,
      'hashed_pass' => $this->hashUserPassword($account, $timestamp),
    ], $url_options);
  }

  /**
   * Check user password hash validity.
   *
   * Test whether the received hash matches one generated using the same
   * intended inputs.
   *
   * @param string $hash
   * @param \Drupal\user\UserInterface $user
   * @param int $timestamp
   *
   * @return bool
   */
  public function checkHash(string $hash, UserInterface $user, int $timestamp): Bool {
    $user = $user ?: $this->currentUser;
    return hash_equals($hash, $this->hashUserPassword($user, $timestamp));
  }

  /**
   * Creates a unique hash value for use in time-dependent per-user URLs.
   *
   * This hash is normally used to build a unique and secure URL that is sent to
   * the user by email for purposes such as resetting the user's password. In
   * order to validate the URL, the same hash can be generated again, from the
   * same information, and compared to the hash value from the URL. The hash
   * contains the time stamp, the user's last login time, the numeric user ID,
   * and the user's email address.
   * For a usage example, see user_cancel_url() and
   * \Drupal\user\Controller\UserController::confirmCancel().
   *
   * @internal
   *
   * @param \Drupal\user\UserInterface $account
   *   An object containing the user account.
   * @param int $timestamp
   *   A UNIX timestamp, typically REQUEST_TIME.
   *
   * @return string
   *   A string that is safe for use in URLs and SQL statements.
   */
  private function hashUserPassword(UserInterface $account, $timestamp) {
    $data = $timestamp;
    $data .= $account->getLastLoginTime();
    $data .= $account->id();
    $data .= $account->getEmail();
    return Crypt::hmacBase64($data, Settings::getHashSalt() . $account->getPassword());
  }
}
