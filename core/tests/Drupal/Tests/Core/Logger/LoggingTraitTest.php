<?php

namespace Drupal\Tests\Core\Logger;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\Traits\Core\LoggingTrait;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @coversDefaultClass \Drupal\Tests\Traits\Core\LoggingTrait
 * @group PHPUnit
 */
class LoggingTraitTest extends UnitTestCase {

  use LoggingTrait;

  /**
   * @dataProvider expectLogMetProvider
   */
  public function testExpectLogMet(array $expectation) {
    $this->expectLog(...$expectation);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->assertLogExpectationsMet();
  }

  public function expectLogMetProvider() {
    return [
      [RfcLogLevel::WARNING, 'channel_a'],
      [RfcLogLevel::WARNING, 'channel_a', 'some message'],
      [RfcLogLevel::WARNING, 'channel_a', 'message'],
      [RfcLogLevel::WARNING, 'channel_a', 'some'],
    ];
  }

  /**
   * @dataProvider expectLogUnmetProvider
   */
  public function testExpectLogUnmet(array $expectation) {
    $this->expectLog(...$expectation);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->assertNotEmpty($this->expectedLogs);
  }

  public function expectLogUnmetProvider() {
    return [
      [RfcLogLevel::WARNING, 'channel_b'],
      [RfcLogLevel::WARNING, 'channel_a', 'some other message'],
      [RfcLogLevel::ERROR, 'channel_a'],
      [RfcLogLevel::ERROR, 'channel_a', 'some message'],
      [RfcLogLevel::ERROR, 'channel_b'],
      [RfcLogLevel::ERROR, 'channel_b', 'some message'],
      [RfcLogLevel::NOTICE, 'channel_a'],
      [RfcLogLevel::NOTICE, 'channel_a', 'some message'],
      [RfcLogLevel::NOTICE, 'channel_b'],
      [RfcLogLevel::NOTICE, 'channel_b', 'some message'],
    ];
  }

  /**
   * @dataProvider expectNoLogMetProvider
   */
  public function testExpectNoLogMet(array $expectation) {
    $this->expectNoLogMoreSevereThan(...$expectation);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->expectNotToPerformAssertions();
  }

  public function expectNoLogMetProvider() {
    return [
      [RfcLogLevel::NOTICE],
      [RfcLogLevel::NOTICE, 'channel_a'],
      [RfcLogLevel::NOTICE, 'channel_a', 'some'],
      [RfcLogLevel::NOTICE, 'channel_a', 'message'],
      [RfcLogLevel::NOTICE, 'channel_a', 'some message'],
      [RfcLogLevel::NOTICE, 'channel_a', 'some other message'],
      [RfcLogLevel::NOTICE, 'channel_b'],
      [RfcLogLevel::NOTICE, 'channel_b', 'some'],
      [RfcLogLevel::NOTICE, 'channel_b', 'message'],
      [RfcLogLevel::NOTICE, 'channel_b', 'some message'],
      [RfcLogLevel::NOTICE, 'channel_b', 'some other message'],
      [RfcLogLevel::WARNING, 'channel_a', 'some other message'],
      [RfcLogLevel::WARNING, 'channel_b'],
      [RfcLogLevel::WARNING, 'channel_b', 'some'],
      [RfcLogLevel::WARNING, 'channel_b', 'message'],
      [RfcLogLevel::WARNING, 'channel_b', 'some message'],
      [RfcLogLevel::WARNING, 'channel_b', 'some other message'],
      [RfcLogLevel::ERROR, 'channel_a', 'some other message'],
      [RfcLogLevel::ERROR, 'channel_b'],
      [RfcLogLevel::ERROR, 'channel_b', 'some'],
      [RfcLogLevel::ERROR, 'channel_b', 'message'],
      [RfcLogLevel::ERROR, 'channel_b', 'some message'],
      [RfcLogLevel::ERROR, 'channel_b', 'some other message'],
    ];
  }

  /**
   * @dataProvider expectNoLogUnmetProvider
   */
  public function testExpectNoLogUnmet(array $expectation) {
    $this->expectNoLogMoreSevereThan(...$expectation);
    // These calls to allowLog() should have no consequences
    // because the actual log will use a different channel.
    $this->allowLog(RfcLogLevel::WARNING, 'channel_b');
    $this->allowLog(RfcLogLevel::WARNING, 'channel_b', 'some message');
    $this->expectException(ExpectationFailedException::class);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
  }

  public function expectNoLogUnmetProvider() {
    return [
      [RfcLogLevel::WARNING],
      [RfcLogLevel::WARNING, 'channel_a'],
      [RfcLogLevel::WARNING, 'channel_a', 'some'],
      [RfcLogLevel::WARNING, 'channel_a', 'message'],
      [RfcLogLevel::WARNING, 'channel_a', 'some message'],
      [RfcLogLevel::ERROR],
      [RfcLogLevel::ERROR, 'channel_a'],
      [RfcLogLevel::ERROR, 'channel_a', 'some'],
      [RfcLogLevel::ERROR, 'channel_a', 'message'],
      [RfcLogLevel::ERROR, 'channel_a', 'some message'],
    ];
  }

  /**
   * @dataProvider allowLogProvider
   */
  public function testAllowLogLevel(array $expectation) {
    $this->allowLog(...$expectation);
    $this->expectNoLogMoreSevereThan(RfcLogLevel::WARNING);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->expectNotToPerformAssertions();
  }

  /**
   * @dataProvider allowLogProvider
   */
  public function testAllowLogLevelChannel(array $expectation) {
    $this->allowLog(...$expectation);
    $this->expectNoLogMoreSevereThan(RfcLogLevel::WARNING, 'channel_a');
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->expectNotToPerformAssertions();
  }

  public function allowLogProvider() {
    return [
      [RfcLogLevel::WARNING, 'channel_a'],
      [RfcLogLevel::WARNING, 'channel_a', 'some'],
      [RfcLogLevel::WARNING, 'channel_a', 'message'],
      [RfcLogLevel::WARNING, 'channel_a', 'some message'],
      [RfcLogLevel::ERROR, 'channel_a'],
      [RfcLogLevel::ERROR, 'channel_a', 'some'],
      [RfcLogLevel::ERROR, 'channel_a', 'message'],
      [RfcLogLevel::ERROR, 'channel_a', 'some message'],
    ];
  }

  /**
   * @dataProvider expectLogMetProvider
   */
  public function testExpectLogBeforeExpectNoLog(array $expectation) {
    $this->expectLog(...$expectation);
    $this->expectNoLogMoreSevereThan(RfcLogLevel::WARNING);
    $this->handleLog(RfcLogLevel::WARNING, 'channel_a', 'some message');
    $this->expectNotToPerformAssertions();
  }

}
