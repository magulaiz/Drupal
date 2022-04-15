<?php

namespace Drupal\Tests\Traits\Core;

use Drupal\Core\Logger\RfcLoggerTrait;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Sets test expectations for generated log messages.
 *
 * A test class using this trait should:
 * - declare that it implements \Psr\Log\LoggerInterface,
 * - call LoggingTrait::addAsLogger in its setUp() method, and
 * - call LoggingTrait::assertLogExpectationsMet() in its
 * assertPostConditions() method.
 *
 * In order to assert that a test does or does not generate logs, the test
 * must call LoggingTrait::expectLog() or
 * LoggingTrait::expectNoLogMoreSevereThan(); it may also call
 * LoggingTrait::allowLog().
 */
trait LoggingTrait {

  use RfcLoggerTrait;

  /**
   * The messages that this test expects to be logged.
   */
  protected array $expectedLogs = [];

  /**
   * The messages that this test expects will not be logged.
   */
  protected array $disallowedLogs = [];

  /**
   * The messages that this test allows but does not expect.
   */
  protected array $allowedLogs = [];

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
    $count = 1 + ($this->expectedLogs[$level][$channel][$message] ?? 0);
    $this->expectedLogs[$level][$channel][$message] = $count;
  }

  /**
   * Setup an expectation that a test will not generate a log message.
   *
   * If a matching log message is generated, the test will fail. Log
   * messages of the specified level or more severe will trigger a test
   * to fail as soon as they are received.
   *
   * Log messages that are set up as expected (by ::expectLog()) or
   * are set up as allowed (by ::allowLog()) are exempt and will not
   * trigger failure.
   *
   * @param int $level
   *   A log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   (optional) A logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function expectNoLogMoreSevereThan($level, $channel = '', $message = '') {
    $this->disallowedLogs[$channel][$message] = $level;
  }

  /**
   * Define a certain kind of log message as allowed.
   *
   * If a generated log message matches the specified parameters, then
   * it will not cause a test to fail even if would otherwise have been
   * disallowed (by ::expectNoLogMoreSevereThan()).
   *
   * Typically ::expectNoLogMoreSevereThan() is used to define a broad class of
   * unacceptable log messages, e.g. 'fail all warnings and above', and
   * then allowLog() is used to define an narrower exception to that,
   * e.g. 'except allow warnings from the user channel'.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function allowLog($level, $channel, $message = '') {
    $this->allowedLogs[$channel][$message] = $level;
  }

  /**
   * Process a log message received by the test.
   *
   * If the log message is expected, it is tracked so that
   * expectations can be verified at the end of the test. If the
   * log message is not allowed, the test is failed immediately.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   */
  protected function handleLog($level, $channel, $message) {
    $isExpected = $this->handleLogExpectations($level, $channel, $message);
    if ($isExpected) {
      return;
    }

    $isDisallowed = !$this->isLogAllowed($level, $channel, $message) && $this->isLogDisallowed($level, $channel, $message);
    if ($isDisallowed) {
      throw new ExpectationFailedException("Disallowed log message received: $level $channel $message");
    }
  }

  /**
   * Process a log message received by the test in the context of expectations.
   *
   * If the log message matches an expected type of log message,
   * then the count of outstanding expectations of that type is reduced.
   * Messages can match partially, and against the wildcard empty string expectation.
   * If messages match against multiple expectations, then the count of all are reduced.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   */
  protected function handleLogExpectations($level, $channel, $message) {
    if (isset($this->expectedLogs[$level][$channel])) {
      foreach ($this->expectedLogs[$level][$channel] as $expectedMessage => $count) {
        if ($expectedMessage === '' || strpos($message, $expectedMessage) !== FALSE) {
          // If the count was 1, then the expectation is now fully met.
          if ($count === 1) {
            unset($this->expectedLogs[$level][$channel][$expectedMessage]);
            // Clear out empty expectation arrays to facilitate asserting that there are
            // no unmet expectations at the end of the test.
            if (empty($this->expectedLogs[$level][$channel])) {
              unset($this->expectedLogs[$level][$channel]);
            }
            if (empty($this->expectedLogs[$level])) {
              unset($this->expectedLogs[$level]);
            }
          }
          // If the count was more than 1, decrement the expectation.
          else {
            $this->expectedLogs[$level][$channel][$expectedMessage] = $count - 1;
          }
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Determine if a log message is allowed.
   *
   * A received log message is compared against a set of rules set up
   * earlier to see if there is a match. A rule matches a log message if:
   * - it has the same channel specified or no channel specified
   * - its message is empty or is contained in the actual log message
   * - its level is less than or equally severe as the log level
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   */
  protected function isLogAllowed($level, $channel, $message) {
    $channels = [$channel, ''];
    foreach ($channels as $channel) {
      $allowed = $this->allowedLogs[$channel] ?? [];
      foreach ($allowed as $allowedMessage => $allowedLevel) {
        if ($allowedMessage === '' || strpos($message, $allowedMessage) !== FALSE) {
          if ($level >= $allowedLevel) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Determine if a log message is disallowed.
   *
   * A received log message is compared against a set of rules set up
   * earlier to see if there is a match. A rule matches a log message if:
   * - it has the same channel specified or no channel specified
   * - its message is empty or is contained in the actual log message
   * - its level is more than or equally severe as the log level
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   */
  protected function isLogDisallowed($level, $channel, $message) {
    $channels = [$channel, ''];
    foreach ($channels as $channel) {
      $disallowed = $this->disallowedLogs[$channel] ?? [];
      foreach ($disallowed as $disallowedMessage => $disallowedLevel) {
        if ($disallowedMessage === '' || strpos($message, $disallowedMessage) !== FALSE) {
          if ($level <= $disallowedLevel) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * Assert that no logs were expected that have not been received.
   */
  protected function assertLogExpectationsMet() {
    $this->assertEmpty($this->expectedLogs);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->handleLog($level, $context['channel'] ?? '', $message);
  }

}
