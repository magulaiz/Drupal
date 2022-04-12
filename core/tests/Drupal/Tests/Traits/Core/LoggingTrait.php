<?php

namespace Drupal\Tests\Traits\Core;

use Drupal\Core\Logger\RfcLoggerTrait;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Sets test expectations for generated log messages.
 *
 * A test class using this trait should declare that it implements
 * \Psr\Log\LoggerInterface, should call LoggingTrait::addAsLogger in its
 * setUp() method, and should call LoggingTrait::assertLogExpectationsMet() in
 *  its assertPostConditions() method.
 *
 * In order to assert that a test does or does not generate logs, the test
 * must call LoggingTrait::expectLog() or LoggingTrait::expectNoLog(); it may
 * also call LoggingTrait::allowLog().
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
   * messages of the specified level or greater will trigger a test
   * to fail as soon as they are received.
   *
   * Log messages that are set up as expected (by ::expectLog()) or
   * are set up as allowed (by ::allowLog()) are exempt and will not
   * trigger failure.
   *
   * @param int $level
   *   (optional) The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   (optional) The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  protected function expectNoLog($level = '', $channel = '', $message = '') {
    $this->disallowedLogs[$channel][$message] = $level;
  }

  /**
   * Define a certain kind of log message as allowed.
   *
   * If a generated log message matches the specified parameters, then
   * it will not cause a test to fail even if would otherwise have been
   * disallowed (by ::expectNoLog()).
   *
   * Typically expectNoLog() is used to define a broad class of
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
    $isExpected = $this->handleLogExpectations($level, $channel, $message) || $this->handleLogExpectations($level, $channel, '');
    if ($isExpected) {
      return;
    }

    $isAllowed = $this->isLogAllowed($this->allowedLogs, TRUE, $level, $channel, $message);
    if (!$isAllowed) {
      $isDisallowed = $this->isLogAllowed($this->disallowedLogs, FALSE, $level, $channel, $message);
      if ($isDisallowed) {
        throw new ExpectationFailedException("Disallowed log message received: $level $channel $message");
      }
    }
  }

  /**
   * Process a log message received by the test in the context of expectations.
   *
   * If the log message matches an expected type of log message,
   * then the count of outstanding expectations of that type is reduced.
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
      foreach ($this->expectedLogs[$level][$channel] as $expectedMessage) {
        if (strpos($message, $expectedMessage) !== FALSE) {
          $this->expectedLogs[$level][$channel][$expectedMessage] = $this->expectedLogs[$level][$channel][$expectedMessage] - 1;
          if ($this->expectedLogs[$level][$channel][$expectedMessage] === 0) {
            unset($this->expectedLogs[$level][$channel][$expectedMessage]);
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
   * - it's message is contained in the actual log message
   * - it's level is more or less than the log level, depending on $allowed
   *
   * @param array $rules
   *   A set of log rules set up earlier.
   * @param bool $allowed
   *   Whether to match level greater or lesser than the rule.
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   */
  protected function isLogAllowed(array $rules, $allowed, $level, $channel, $message) {
    $channels = [$channel, ''];
    foreach ($channels as $channel) {
      $channelRules = $rules[$channel];
      foreach ($channelRules as $ruleMessage => $ruleLevel) {
        if (strpos($message, $ruleMessage) !== FALSE || $ruleMessage === '') {
          if (($allowed && $ruleLevel >= $level) ||
          (!$allowed && $ruleLevel <= $level)){
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
   * Register a test as a logger.
   */
  protected function addAsLogger() {
    $this->container->get('logger.factory')->addLogger($this);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->handleLog($level, $context['channel'] ?? '', $message);
  }

}
