<?php

namespace Drupal\KernelTests;

use Drupal\Component\Datetime\TimeInterface;

/**
 * Provides a dummy time service that can be used for testing.
 *
 * Time can be advanced manually for testing purposes.
 */
class TestTime implements TimeInterface {

  /**
   * The request micro time to return.
   *
   * @var float
   */
  protected $requestMicroTime;

  /**
   * The request time to return.
   *
   * @var int
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

  /**
   * {@inheritdoc}
   */
  public function getCurrentTime() {
    return time();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentMicroTime() {
    return microtime(TRUE);
  }

}
