<?php

namespace Drupal\KernelTests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

/**
 * Logger that allows asserting logs generated during tests.
 */
class AssertableLogger implements LoggerInterface {

  use RfcLoggerTrait;

  /**
   * Logs that are expected to be generated.
   */
  protected array $expectedLogCriteria = [];

  /**
   * Logs that are allowed but not expected.
   */
  protected array $allowedLogCriteria = [];

  /**
   * Logs that are expected not to be generated.
   */
  protected array $disallowedLogCriteria = [];

  /**
   * Logs that are disallowed and have been generated.
   */
  protected array $disallowedLogs = [];

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->handleLog($level, $context['channel'] ?? '', $message, $context);
  }

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
  public function expectLog(int $level, string $channel, string $message = ''): void {
    $count = 1 + ($this->expectedLogCriteria[$level][$channel][$message] ?? 0);
    $this->expectedLogCriteria[$level][$channel][$message] = $count;
  }

  /**
   * Setup an expectation that a test will not generate a log message.
   *
   * If a matching log message is generated, the test will fail. Log
   * messages that are set up as expected (by ::expectLog()) or are set up as
   * allowed (by ::allowLogsAsSevereAs()) are exempt and will not trigger
   * failure.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   (optional) The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  public function expectNoLogsAsSevereAs(int $level, string $channel = '', string $message = ''): void {
    $this->disallowedLogCriteria[$channel][$message] = $level;
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
   * then allowLogsAsSevereAs() is used to define an narrower exception to
   * that, e.g. 'except allow warnings from the user channel'.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   (optional) Text that the log message must contain.
   */
  public function allowLogsAsSevereAs(int $level, string $channel, string $message = ''): void {
    $this->allowedLogCriteria[$channel][$message] = $level;
  }

  /**
   * Get the log expectations that have not yet been met.
   *
   * @return array
   *   Counts of unmet log expectations keyed by level, channel and message.
   */
  public function getUnmetExpectations(): array {
    return $this->expectedLogCriteria;
  }

  /**
   * Get the logs that have been received but not should not have been.
   *
   * @return array
   *   The disallowed logs, as arrays with level, channel, message and trace.
   */
  public function getDisallowedLogs(): array {
    return $this->disallowedLogs;
  }

  /**
   * Process a generated log message.
   *
   * If the log message is expected, it is removed from the outstanding
   * expectations. If the log message is disallowed, it is stored so it
   * can be reported later.
   *
   * @param int $level
   *   The log level as defined in Drupal\Core\Logger\RfcLogLevel.
   * @param string $channel
   *   The logger channel.
   * @param string $message
   *   The log message.
   * @param array $context
   *   The log context array.
   */
  protected function handleLog(int $level, string $channel, string $message, array $context): void {
    $is_expected = $this->handleLogExpectations($level, $channel, $message);
    if ($is_expected) {
      return;
    }
    $is_disallowed = !$this->isLogAllowed($level, $channel, $message) && $this->isLogDisallowed($level, $channel, $message);
    if ($is_disallowed) {
      // Fill in any placeholders with values from the log context.
      $placeholders = preg_grep('/^[@%:]/', array_keys($context));
      $message = new FormattableMarkup($message, array_intersect_key($context, array_flip($placeholders)));

      $e = new \Exception();
      $trace = explode("\n", $e->getTraceAsString());
      $this->disallowedLogs[] = [
        'level' => $level,
        'channel' => $channel,
        'message' => (string) $message,
        'trace' => $trace,
      ];
    }
  }

  /**
   * Process a generated log message, comparing against expectations.
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
   *
   * @return bool
   *   Whether or not the log was expected.
   */
  protected function handleLogExpectations(int $level, string $channel, string $message): bool {
    if (isset($this->expectedLogCriteria[$level][$channel])) {
      foreach ($this->expectedLogCriteria[$level][$channel] as $expected_message => $count) {
        if ($expected_message === '' || strpos($message, $expected_message) !== FALSE) {
          // If the count was 1, then the expectation is now fully met.
          if ($count === 1) {
            unset($this->expectedLogCriteria[$level][$channel][$expected_message]);
            // Clear out empty expectation arrays to facilitate asserting that there are
            // no unmet expectations at the end of the test.
            if (empty($this->expectedLogCriteria[$level][$channel])) {
              unset($this->expectedLogCriteria[$level][$channel]);
            }
            if (empty($this->expectedLogCriteria[$level])) {
              unset($this->expectedLogCriteria[$level]);
            }
          }
          // If the count was more than 1, decrement the expectation.
          else {
            $this->expectedLogCriteria[$level][$channel][$expected_message] = $count - 1;
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
   *
   * @return bool
   *   Whether or not the log matches the allowed LogCriteria.
   */
  protected function isLogAllowed(int $level, string $channel, string $message): bool {
    $channels = [$channel, ''];
    foreach ($channels as $channel) {
      $allowed = $this->allowedLogCriteria[$channel] ?? [];
      foreach ($allowed as $allowed_message => $allowed_level) {
        if ($allowed_message === '' || strpos($message, $allowed_message) !== FALSE) {
          if ($level >= $allowed_level) {
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
   *
   * @return bool
   *   Whether or not the log matches the disallowed LogCriteria.
   */
  protected function isLogDisallowed(int $level, string $channel, string $message): bool {
    $channels = [$channel, ''];
    foreach ($channels as $channel) {
      $disallowed = $this->disallowedLogCriteria[$channel] ?? [];
      foreach ($disallowed as $disallowed_message => $disallowed_level) {
        if ($disallowed_message === '' || strpos($message, $disallowed_message) !== FALSE) {
          if ($level <= $disallowed_level) {
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

}
