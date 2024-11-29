<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Logger;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\Core\Logger\LoggerChannel
 * @group Logger
 */
class LoggerChannelTest extends UnitTestCase {

  /**
   * Tests LoggerChannel::log().
   *
   * @param callable $expected
   *   An anonymous function to use with $this->callback() of the logger mock.
   *   The function should check the $context array for expected values.
   * @param bool $request
   *   Whether to pass a request to the channel under test.
   * @param bool $account
   *   Whether to pass an account to the channel under test.
   *
   * @dataProvider providerTestLog
   * @covers ::log
   * @covers ::isIgnoredLog
   * @covers ::setCurrentUser
   * @covers ::setRequestStack
   */
  public function testLog(callable $expected, bool $request = FALSE, bool $account = FALSE): void {
    $channel_name = 'test';
    $severity_level = rand(RfcLogLevel::EMERGENCY, RfcLogLevel::DEBUG);
    $channel = $this->getMockBuilder(LoggerChannel::class)
      ->onlyMethods(['isIgnoredLog'])
      ->setConstructorArgs([$channel_name])
      ->getMock();
    $message = $this->randomMachineName();
    $logger = $this->createMock('Psr\Log\LoggerInterface');
    $logger->expects($this->once())
      ->method('log')
      ->with($this->anything(), $message, $this->callback($expected));
    $channel->expects($this->once())
      ->method('isIgnoredLog')
      ->with($channel_name, $severity_level, get_class($logger));
    $channel->addLogger($logger);
    if ($request) {
      $request_mock = $this->getMockBuilder(Request::class)
        ->onlyMethods(['getClientIp'])
        ->getMock();
      $request_mock->expects($this->any())
        ->method('getClientIp')
        ->willReturn('127.0.0.1');
      $request_mock->headers = $this->createMock(HeaderBag::class);

      $requestStack = new RequestStack();
      $requestStack->push($request_mock);
      $channel->setRequestStack($requestStack);
    }
    if ($account) {
      $account_mock = $this->createMock(AccountInterface::class);
      $account_mock->expects($this->any())
        ->method('id')
        ->willReturn(1);

      $channel->setCurrentUser($account_mock);
    }
    $channel->log($severity_level, $message);
  }

  /**
   * Tests LoggerChannel::isIgnoredLog().
   *
   * @param array $ignore_logs
   *   Ignore log rules which should be defined in settings.php.
   * @param array $sub_cases
   *   Cases to assert.
   *
   * @dataProvider providerTestIsIgnoredLog
   * @covers ::isIgnoredLog
   */
  public function testIsIgnoredLog(array $ignore_logs, array $sub_cases): void {
    new Settings([
      'ignore_logs' => $ignore_logs,
    ]);

    foreach ($sub_cases as $case) {
      $channel = new LoggerChannelTestable($case['params'][0]);
      $this->assertEquals($channel->isIgnoredLog(...$case['params']), $case['result']);
    }
  }

  /**
   * Tests LoggerChannel::log() recursion protection.
   *
   * @covers ::log
   */
  public function testLogRecursionProtection(): void {
    $channel = new LoggerChannel('test');
    $logger = $this->createMock('Psr\Log\LoggerInterface');
    $logger->expects($this->exactly(LoggerChannel::MAX_CALL_DEPTH))
      ->method('log');
    $channel->addLogger($logger);
    $channel->addLogger(new NaughtyRecursiveLogger($channel));
    $channel->log(rand(RfcLogLevel::EMERGENCY, RfcLogLevel::DEBUG), $this->randomMachineName());
  }

  /**
   * Tests LoggerChannel::addLoggers().
   *
   * @covers ::addLogger
   * @covers ::sortLoggers
   */
  public function testSortLoggers(): void {
    $channel = new LoggerChannel($this->randomMachineName());
    $index_order = '';
    for ($i = 0; $i < 4; $i++) {
      $logger = $this->createMock('Psr\Log\LoggerInterface');
      $logger->expects($this->once())
        ->method('log')
        ->willReturnCallback(function () use ($i, &$index_order) {
          // Append the $i to the index order, so that we know the order that
          // loggers got called with.
          $index_order .= $i;
        });
      $channel->addLogger($logger, $i);
    }

    $channel->log(rand(RfcLogLevel::EMERGENCY, RfcLogLevel::DEBUG), $this->randomMachineName());
    // Ensure that the logger added in the end fired first.
    $this->assertEquals('3210', $index_order);
  }

  /**
   * Tests that $context['ip'] is a string even when the request's IP is NULL.
   */
  public function testNullIp(): void {
    // Create a logger that will fail if $context['ip'] is not an empty string.
    $logger = $this->createMock(LoggerInterface::class);
    $expected = function ($context) {
      return $context['channel'] == 'test' && $context['ip'] === '';
    };
    $logger->expects($this->once())
      ->method('log')
      ->with($this->anything(), 'Test message', $this->callback($expected));

    // Set up a request stack that has a request that will return NULL when
    // ::getClientIp() is called.
    $requestStack = new RequestStack();
    $request_mock = $this->getMockBuilder(Request::class)
      ->onlyMethods(['getClientIp'])
      ->getMock();
    $request_mock->expects($this->any())
      ->method('getClientIp')
      ->willReturn(NULL);
    $requestStack->push($request_mock);

    // Set up the logger channel for testing.
    $channel = new LoggerChannel('test');
    $channel->addLogger($logger);
    $channel->setRequestStack($requestStack);

    // Perform the test.
    $channel->log(rand(0, 7), 'Test message');
  }

  /**
   * Data provider for self::testLog().
   */
  public static function providerTestLog(): \Generator {
    // No request or account.
    yield [
      function ($context) {
        return $context['channel'] == 'test' && empty($context['uid']) && $context['ip'] === '';
      },
      FALSE,
      FALSE,
    ];

    // With account but not request. Since the request is not available the
    // current user should not be used.
    yield [
      function ($context) {
        return $context['uid'] === 0 && $context['ip'] === '';
      },
      FALSE,
      TRUE,
    ];

    // With request but not account.
    yield [
      function ($context) {
        return $context['ip'] === '127.0.0.1' && empty($context['uid']);
      },
      TRUE,
      FALSE,
    ];

    // Both request and account.
    yield [
      function ($context) {
        return $context['ip'] === '127.0.0.1' && $context['uid'] === 1;
      },
      TRUE,
      TRUE,
    ];
  }

  /**
   * Data provider for self::testIsIgnoredLog().
   */
  public static function providerTestIsIgnoredLog(): array {
    // Full match.
    $cases['full_match'] = [
      [
        [
          'channel' => 'test_channel',
          'level' => LogLevel::DEBUG,
          'logger' => 'test_logger',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => FALSE,
        ],
      ],
    ];

    // All loggers match.
    $cases['all_loggers_match'] = [
      [
        [
          'channel' => 'test_channel',
          'level' => LogLevel::DEBUG,
          'logger' => '*',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => TRUE,
        ],
      ],
    ];

    // All levels match.
    $cases['all_levels_match'] = [
      [
        [
          'channel' => 'test_channel',
          'level' => '*',
          'logger' => 'test_logger',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => FALSE,
        ],
      ],
    ];

    // All levels and all loggers match.
    $cases['all_levels_and_all_loggers_match'] = [
      [
        [
          'channel' => 'test_channel',
          'level' => '*',
          'logger' => '*',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => TRUE,
        ],
      ],
    ];

    // All channels match.
    $cases['all_channels_match'] = [
      [
        [
          'channel' => '*',
          'level' => LogLevel::DEBUG,
          'logger' => 'test_logger',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => FALSE,
        ],
      ],
    ];

    // All channels and all loggers match.
    $cases['all_channels_and_all_loggers_match'] = [
      [
        [
          'channel' => '*',
          'level' => LogLevel::DEBUG,
          'logger' => '*',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => FALSE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => TRUE,
        ],
      ],
    ];

    // All channels and all levels match.
    $cases['all_channels_and_all_levels_match'] = [
      [
        [
          'channel' => '*',
          'level' => '*',
          'logger' => 'test_logger',
        ],
      ],
      [
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel_a',
            RfcLogLevel::DEBUG,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::INFO,
            'test_logger',
          ],
          'result' => TRUE,
        ],
        [
          'params' => [
            'test_channel',
            RfcLogLevel::DEBUG,
            'test_logger_a',
          ],
          'result' => FALSE,
        ],
      ],
    ];

    return $cases;
  }

}

/**
 * Class NaughtyRecursiveLogger triggers logging during log().
 */
class NaughtyRecursiveLogger implements LoggerInterface {
  use LoggerTrait;

  /**
   * The channel used to log messages.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $channel;

  /**
   * A logged message.
   *
   * @var string
   */
  protected $message;

  /**
   * NaughtyRecursiveLogger constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelInterface $channel
   *   The channel on which to log messages.
   */
  public function __construct(LoggerChannelInterface $channel) {
    $this->channel = $channel;
  }

  /**
   * Log a message.
   *
   * @param mixed $level
   *   The level at which to log the message.
   * @param string|\Stringable $message
   *   The message to log.
   * @param array $context
   *   The message context.
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->channel->log(rand(RfcLogLevel::EMERGENCY, RfcLogLevel::DEBUG), $message, $context);
  }

}

/**
 * Class LoggerChannelTestable changes "isIgnoredLog" visibility scope.
 */
class LoggerChannelTestable extends LoggerChannel {

  /**
   * {@inheritdoc}
   */
  public function isIgnoredLog($channel, $level, $logger) {
    return parent::isIgnoredLog($channel, $level, $logger);
  }

}
