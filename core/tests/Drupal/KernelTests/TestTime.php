<?php

namespace Drupal\KernelTests;

use Drupal\Component\Datetime\Time;

/**
 * Provides a dummy time service that can be used for testing.
 *
 * Time can be advanced manually for testing purposes.
 */
class TestTime extends Time {

  /**
   * The request micro time to return.
   *
   * @var float
   *
   * @see \Drupal\Component\Datetime\Time::getRequestMicroTime()
   */
  protected $requestMicroTime;

  /**
   * The request time to return.
   *
   * @var int
   *
   * @see \Drupal\Component\Datetime\Time::getRequestTime()
   */
  protected $requestTime;

  /**
   * Constructs a new class instance.
   */
  public function __construct() {
    $this->requestMicroTime = microtime(TRUE);
    $this->requestTime = time();
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestMicroTime() {
    return $this->requestMicroTime;
  }

  /**
   * {@inheritdoc}
   */
  public function getRequestTime() {
    return $this->requestTime;
  }

  /**
   * Advances the reported request micro time.
   *
   * @param float $seconds
   *   (optional) Number of seconds by which to advance the reported request
   *   micro time.
   *
   * @return $this
   */
  public function advanceMicroTime($seconds = 1.0) {
    $this->requestMicroTime += $seconds;
    return $this;
  }

  /**
   * Advances the reported request time.
   *
   * @param int $seconds
   *   (optional) Number of seconds by which to advance the reported request
   *   time.
   *
   * @return $this
   */
  public function advanceTime($seconds = 1) {
    $this->requestTime += $seconds;
    return $this;
  }

}
