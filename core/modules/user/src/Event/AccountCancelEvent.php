<?php

namespace Drupal\user\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

/**
 * Provides a user cancel event class.
 */
class AccountCancelEvent extends Event {

  /**
   * The account to be cancelled.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * The account cancellation method to use.
   *
   * @var string
   */
  protected $method;

  /**
   * Context array. Typically, an array of submitted form values.
   *
   * @var array
   */
  protected $context;

  /**
   * Constructs a new event instance.
   *
   * @param \Drupal\user\UserInterface $account
   *   The user account to be cancelled.
   * @param string $method
   *   The account cancellation method to use.
   * @param array $context
   *   Context array. Typically, an array of submitted form values.
   */
  public function __constructor(UserInterface $account, string $method, array $context) {
    $this->account = $account;
    $this->method = $method;
    $this->context = $context;
  }

  /**
   * Returns the user account to be cancelled.
   *
   * @return \Drupal\user\UserInterface
   *   The user account to be cancelled.
   */
  public function getAccount(): UserInterface {
    return $this->account;
  }

  /**
   * Returns the account cancellation method to use.
   *
   * @return string
   *   The account cancellation method to use.
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * Returns the context array. Usually an array of submitted form values.
   *
   * @return array
   *   Context array. Typically, an array of submitted form values.
   */
  public function getContext(): array {
    return $this->context;
  }

}
