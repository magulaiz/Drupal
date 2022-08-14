<?php

namespace Drupal\user\Event;

use Drupal\user\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event triggered just after the user has logged in.
 */
class UserLoginEvent extends Event {

  /**
   * Constructs a new event instance.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of user just logged in.
   */
  public function __construct(protected UserInterface $account) {
  }

  /**
   * Returns the logged-in user account.
   *
   * @return \Drupal\user\UserInterface
   *   The logged-in user account.
   */
  public function getAccount(): UserInterface {
    return $this->account;
  }

}
