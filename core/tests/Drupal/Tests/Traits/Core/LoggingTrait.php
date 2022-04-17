<?php

namespace Drupal\Tests\Traits\Core;

use Drupal\KernelTests\AssertableLogger;

/**
 * Sets test expectations for generated log messages.
 *
 * A test class using this trait should:
 * - call LoggingTrait::getAssertableLogger, typically in its setUp() method, and
 * - call LoggingTrait::assertLogExpectationsMet(), typically in its
 * assertPostConditions() method.
 *
 * In order to assert that a test does or does not generate logs, the test
 * must call LoggingTrait::expectLog() or
 * LoggingTrait::expectNoLogsAsSevereAs(); it may also call
 * LoggingTrait::allowLogs().
 */
trait LoggingTrait {

  /**
   * Setup an expectation that a test will generate a log message.
   *
   * If a matching log message is not generated, the test will fail.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function expectLog($level, $channel, $message = '') {
    $this->getAssertableLogger()->expectLog($level, $channel, $message);
  }

  /**
   * Setup an expectation that a test will not generate a log message.
   *
   * If a matching log message is generated, the test will fail. Log
   * messages of the specified level or more severe will trigger a test
   * to fail as soon as they are received.
   *
   * Log messages that are set up as expected (by ::expectLog()) or
   * are set up as allowed (by ::allowLogs()) are exempt and will not
   * trigger failure.
   *
   * @param int $level
   *   A log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   (optional) A logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function expectNoLogsAsSevereAs($level, $channel = '', $message = '') {
    $this->getAssertableLogger()->expectNoLogsAsSevereAs($level, $channel, $message);
  }

  /**
   * Define a certain kind of log message as allowed.
   *
   * If a generated log message matches the specified parameters, then
   * it will not cause a test to fail even if would otherwise have been
   * disallowed (by ::expectNoLogsAsSevereAs()).
   *
   * Typically ::expectNoLogsAsSevereAs() is used to define a broad class of
   * unacceptable log messages, e.g. 'fail all warnings and above', and
   * then allowLogs() is used to define an narrower exception to that,
   * e.g. 'except allow warnings from the user channel'.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function allowLogs($level, $channel, $message = '') {
    $this->getAssertableLogger()->allowLogs($level, $channel, $message);
  }

  /**
   * Assert that no logs were expected that have not been received.
   */
  protected function assertLogExpectationsMet() {
    if ($this->getAssertableLogger()) {
      $this->assertEmpty($this->getAssertableLogger()->getDisallowedLogs(), "Logs were generated during the test that were explicitly expected not to be generated. " . print_r($this->getAssertableLogger()->getDisallowedLogs(), TRUE));
      $this->assertEmpty($this->getAssertableLogger()->getUnmetExpectations(), "Logs were expected to be generated during the test, but were not. "  . print_r($this->getAssertableLogger()->getUnmetExpectations(), TRUE));
    }
  }

}
