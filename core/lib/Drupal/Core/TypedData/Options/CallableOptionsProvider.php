<?php

namespace Drupal\Core\TypedData\Options;

use Drupal\Core\Session\AccountInterface;

/**
 * A simple options provider based upon a callable.
 */
class CallableOptionsProvider extends SimpleOptionsProviderBase {

  /**
   * The callable providing the options.
   *
   * @var mixed
   */
  protected $callable;

  /**
   * Constructs the object.
   *
   * @param mixed $callable
   *   The callable providing the options.
   */
  public function __construct($callable) {
    $this->callable = $callable;
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(?AccountInterface $account = NULL) {
    return call_user_func($this->callable, $account);
  }

}
