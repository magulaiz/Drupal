<?php

namespace Drupal\Tests\Listeners;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\AssertionFailedError;

/**
 * Fail tests that take too long.
 *
 * This trait listens to PHPUnit-based tests and fails them if they take longer
 * than $timeThreshold to complete. They can avoid this sort of failure if they
 * are annotated as belonging to the $slowGroup group.
 *
 * This time restriction is placed on Drupal tests for two reasons:
 * 1) We can find tests which take a long time and try to make them quicker or
 *   otherwise mitigate them.
 * 2) The test runner can prioritize concurrent test runs so that we run the
 *   longer tests first for greater efficiency.
 */
trait TimeLimitListenerTrait {

  /**
   * The time it takes to fail the test.
   *
   * @var float
   */
  protected $timeThreshold;

  /**
   * The name of the group that marks a test as a slow test.
   *
   * @var string
   */
  protected $slowGroup;

  /**
   * Set the 'slow' group.
   *
   * @param string $slow_group
   *   The group which exempts time-based failures.
   */
  protected function setSlowGroup($slow_group) {
    $this->slowGroup = $slow_group;
  }

  /**
   * Set how long a test can run before it's failed.
   *
   * @param float $time_threshold
   *   The time threshold, in seconds.
   */
  protected function setTimeThreshold($time_threshold) {
    $this->timeThreshold = $time_threshold;
  }

  /**
   * Reacts to the end of a test.
   *
   * @param \PHPUnit\Framework\Test|\PHPUnit_Framework_Test $test
   *   The test object that has ended its test run.
   * @param float $time
   *   The time the test took.
   */
  protected function timeLimitEndTest($test, $time) {
    if (!$this->timeLimitEnabled()) {
      return;
    }
    // We only want to work with concrete tests.
    if ($test instanceof TestCase) {
      $util_test_class = class_exists('PHPUnit_Util_Test') ? 'PHPUnit_Util_Test' : 'PHPUnit\Util\Test';
      $method = $test->getName(FALSE);
      if (in_array($this->slowGroup, $util_test_class::getGroups(get_class($test), $method), TRUE)) {
        return;
      }
      if ($time > $this->timeThreshold) {
        $error = new AssertionFailedError(
          'TIME: Enforcing time limit of ' . $this->timeThreshold . ' seconds. This test took ' . $time . ' seconds, which is too long. Annotate it with @group ' . $this->slowGroup . ' to prevent further failures, or set the DRUPAL_TEST_ENABLE_TIME_LIMIT environment variable to FALSE to disable time limits for tests.'
        );
        $test->getTestResultObject()->addFailure($test, $error, $time);
      }
    }
  }

  /**
   * Determine whether to use the time limit check.
   *
   * Uses the DRUPAL_TEST_ENABLE_TIME_LIMIT environmental variable as the
   * source of truth.
   *
   * @return bool
   *   TRUE if the time limit check should be enabled, FALSE otherwise.
   */
  protected function timeLimitEnabled() {
    if (($enabled = getenv('DRUPAL_TEST_ENABLE_TIME_LIMIT')) !== FALSE) {
      $enabled = strtolower($enabled) == 'true';
    }
    return $enabled;
  }

}
