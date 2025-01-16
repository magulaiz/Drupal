<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Logging;

use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\KernelTests\KernelTestBase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Test system logging functionality.
 *
 * @group Logging
 */
class LoggingTest extends KernelTestBase implements LoggerInterface {
  use RfcLoggerTrait;

  protected static $modules = ['syslog', 'syslog_test'];
  protected $logFileName;

  const CHANNEL_A = 'test_channel_a';
  const CHANNEL_B = 'test_channel_b';
  const DEBUG_MESSAGE = 'debug_message';
  const INFO_MESSAGE = 'info_message';
  const NOTICE_MESSAGE = 'notice_message';
  const WARNING_MESSAGE = 'warning_message';
  const ERROR_MESSAGE = 'error_message';
  const CRITICAL_MESSAGE = 'critical_message';
  const ALERT_MESSAGE = 'alert_message';
  const EMERGENCY_MESSAGE = 'emergency_message';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['syslog']);
    $this->container->get('logger.factory')->addLogger($this);
    $this->logFileName = $this->container->get('file_system')->realpath('public://syslog.log');
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    global $base_url;

    $entry = strtr('!base_url !timestamp !type !ip !request_uri !referer !severity !uid !link !message', [
      '!base_url' => $base_url,
      '!timestamp' => $context['timestamp'],
      '!type' => $context['channel'],
      '!ip' => $context['ip'],
      '!request_uri' => $context['request_uri'],
      '!referer' => $context['referer'],
      '!severity' => $level,
      '!uid' => $context['uid'],
      '!link' => strip_tags($context['link']),
      '!message' => strip_tags($message),
    ]);

    error_log($entry . PHP_EOL, 3, $this->logFileName);
  }

  /**
   * Helper method for logging messages.
   *
   * @param string $channel
   *   The channel on which to log messages.
   */
  protected function fireLogs($channel): void {
    $logger = \Drupal::logger($channel);

    $logger->debug(LoggingTest::DEBUG_MESSAGE);
    $logger->info(LoggingTest::INFO_MESSAGE);
    $logger->notice(LoggingTest::NOTICE_MESSAGE);
    $logger->warning(LoggingTest::WARNING_MESSAGE);
    $logger->error(LoggingTest::ERROR_MESSAGE);
    $logger->critical(LoggingTest::CRITICAL_MESSAGE);
    $logger->alert(LoggingTest::ALERT_MESSAGE);
    $logger->emergency(LoggingTest::EMERGENCY_MESSAGE);
  }

  /**
   * Helper method for getting log records.
   */
  protected function getLogRecords(): array {
    $file_contents = file_exists($this->logFileName) ? file_get_contents($this->logFileName) : '';

    return explode(PHP_EOL, $file_contents);
  }

  /**
   * Assert amount of log records.
   *
   * @param int $count
   *   Amount of log records to assert.
   * @param string $message
   *   Filter for log record message.
   * @param array $records
   *   Array of log records to assert.
   */
  protected function assertLogCount($count, $message, array $records): void {
    $this->assertEquals($count, count(array_filter($records, function ($v) use ($message) {
      return strpos($v, $message);
    })));
  }

  /**
   * Test backward compatibility.
   */
  public function testLoggingEmptyIgnoreConfig(): void {
    $this->fireLogs(LoggingTest::CHANNEL_A);
    $this->fireLogs(LoggingTest::CHANNEL_B);
    $log_records = $this->getLogRecords();

    $this->assertLogCount(16, LoggingTest::CHANNEL_A, $log_records);
    $this->assertLogCount(16, LoggingTest::CHANNEL_B, $log_records);
  }

  /**
   * Test ignore logging.
   *
   * @param array $settings
   *   The 'ignore_logs' settings array.
   * @param int $debug_messages_count
   *   The expected number of debug messages.
   * @param int $info_messages_count
   *   The expected number of info messages.
   * @param int $notice_messages_count
   *   The expected number of notice messages.
   * @param int $warning_messages_count
   *   The expected number of warning messages.
   * @param int $error_messages_count
   *   The expected number of error messages.
   * @param int $critical_messages_count
   *   The expected number of critical messages.
   * @param int $alert_messages_count
   *   The expected number of alert messages.
   * @param int $emergency_messages_count
   *   The expected number of emergency messages.
   * @param int $total_channel_a_messages_count
   *   The expected number of channel A messages.
   * @param int $total_channel_b_messages_count
   *   The expected number of channel B messages.
   *
   * @dataProvider providerTestIgnoreLogging
   */
  public function testIgnoreLogging(
    $settings,
    $debug_messages_count,
    $info_messages_count,
    $notice_messages_count,
    $warning_messages_count,
    $error_messages_count,
    $critical_messages_count,
    $alert_messages_count,
    $emergency_messages_count,
    $total_channel_a_messages_count,
    $total_channel_b_messages_count,
  ): void {
    $this->setSetting('ignore_logs', $settings);

    $this->fireLogs(LoggingTest::CHANNEL_A);
    $this->fireLogs(LoggingTest::CHANNEL_B);
    $log_records = $this->getLogRecords();

    $this->assertLogCount($debug_messages_count, LoggingTest::DEBUG_MESSAGE, $log_records);
    $this->assertLogCount($info_messages_count, LoggingTest::INFO_MESSAGE, $log_records);
    $this->assertLogCount($notice_messages_count, LoggingTest::NOTICE_MESSAGE, $log_records);
    $this->assertLogCount($warning_messages_count, LoggingTest::WARNING_MESSAGE, $log_records);
    $this->assertLogCount($error_messages_count, LoggingTest::ERROR_MESSAGE, $log_records);
    $this->assertLogCount($critical_messages_count, LoggingTest::CRITICAL_MESSAGE, $log_records);
    $this->assertLogCount($alert_messages_count, LoggingTest::ALERT_MESSAGE, $log_records);
    $this->assertLogCount($emergency_messages_count, LoggingTest::EMERGENCY_MESSAGE, $log_records);
    $this->assertLogCount($total_channel_a_messages_count, LoggingTest::CHANNEL_A, $log_records);
    $this->assertLogCount($total_channel_b_messages_count, LoggingTest::CHANNEL_B, $log_records);
  }

  /**
   * Data provider for self::testIgnoreLogging().
   */
  public static function providerTestIgnoreLogging(): array {
    $cases['full_match'] = [
      'settings' => [
        [
          'channel' => LoggingTest::CHANNEL_A,
          'level' => LogLevel::DEBUG,
          'logger' => LoggingTest::class,
        ],
      ],
      'debug_messages_count' => 3,
      'info_messages_count' => 4,
      'notice_messages_count' => 4,
      'warning_messages_count' => 4,
      'error_messages_count' => 4,
      'critical_messages_count' => 4,
      'alert_messages_count' => 4,
      'emergency_messages_count' => 4,
      'total_channel_a_messages_count' => 15,
      'total_channel_b_messages_count' => 16,
    ];

    $cases['all_loggers_match'] = [
      'settings' => [
        [
          'channel' => LoggingTest::CHANNEL_A,
          'level' => LogLevel::DEBUG,
          'logger' => '*',
        ],
      ],
      'debug_messages_count' => 2,
      'info_messages_count' => 4,
      'notice_messages_count' => 4,
      'warning_messages_count' => 4,
      'error_messages_count' => 4,
      'critical_messages_count' => 4,
      'alert_messages_count' => 4,
      'emergency_messages_count' => 4,
      'total_channel_a_messages_count' => 14,
      'total_channel_b_messages_count' => 16,
    ];

    $cases['all_levels_match'] = [
      'settings' => [
        [
          'channel' => LoggingTest::CHANNEL_A,
          'level' => '*',
          'logger' => LoggingTest::class,
        ],
      ],
      'debug_messages_count' => 3,
      'info_messages_count' => 3,
      'notice_messages_count' => 3,
      'warning_messages_count' => 3,
      'error_messages_count' => 3,
      'critical_messages_count' => 3,
      'alert_messages_count' => 3,
      'emergency_messages_count' => 3,
      'total_channel_a_messages_count' => 8,
      'total_channel_b_messages_count' => 16,
    ];

    $cases['all_levels_and_all_loggers_match'] = [
      'settings' => [
        [
          'channel' => LoggingTest::CHANNEL_A,
          'level' => '*',
          'logger' => '*',
        ],
      ],
      'debug_messages_count' => 2,
      'info_messages_count' => 2,
      'notice_messages_count' => 2,
      'warning_messages_count' => 2,
      'error_messages_count' => 2,
      'critical_messages_count' => 2,
      'alert_messages_count' => 2,
      'emergency_messages_count' => 2,
      'total_channel_a_messages_count' => 0,
      'total_channel_b_messages_count' => 16,
    ];

    $cases['all_channels_match'] = [
      'settings' => [
        [
          'channel' => '*',
          'level' => LogLevel::DEBUG,
          'logger' => LoggingTest::class,
        ],
      ],
      'debug_messages_count' => 2,
      'info_messages_count' => 4,
      'notice_messages_count' => 4,
      'warning_messages_count' => 4,
      'error_messages_count' => 4,
      'critical_messages_count' => 4,
      'alert_messages_count' => 4,
      'emergency_messages_count' => 4,
      'total_channel_a_messages_count' => 15,
      'total_channel_b_messages_count' => 15,
    ];

    $cases['all_channels_and_all_loggers_match'] = [
      'settings' => [
        [
          'channel' => '*',
          'level' => LogLevel::DEBUG,
          'logger' => '*',
        ],
      ],
      'debug_messages_count' => 0,
      'info_messages_count' => 4,
      'notice_messages_count' => 4,
      'warning_messages_count' => 4,
      'error_messages_count' => 4,
      'critical_messages_count' => 4,
      'alert_messages_count' => 4,
      'emergency_messages_count' => 4,
      'total_channel_a_messages_count' => 14,
      'total_channel_b_messages_count' => 14,
    ];

    $cases['all_channels_and_all_levels_match'] = [
      'settings' => [
        [
          'channel' => '*',
          'level' => '*',
          'logger' => LoggingTest::class,
        ],
      ],
      'debug_messages_count' => 2,
      'info_messages_count' => 2,
      'notice_messages_count' => 2,
      'warning_messages_count' => 2,
      'error_messages_count' => 2,
      'critical_messages_count' => 2,
      'alert_messages_count' => 2,
      'emergency_messages_count' => 2,
      'total_channel_a_messages_count' => 8,
      'total_channel_b_messages_count' => 8,
    ];

    return $cases;
  }

}
