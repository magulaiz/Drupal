<?php

namespace Drupal\user\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a user authentication event for event listeners.
 */
class UserAuthenticationEvent extends Event {

  /**
   * The user object on which the operation was just performed.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Constructs a user authentication event object.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user object on which the operation was just performed.
   */
  public function __construct(AccountInterface $user) {
    $this->user = $user;
  }

  /**
   * Gets the user object.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user participating in authentication.
   */
  public function getUser(): object {
    return $this->user;
  }

}
