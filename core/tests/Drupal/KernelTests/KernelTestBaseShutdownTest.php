<?php

namespace Drupal\KernelTests;

use Drupal\Core\Shutdown\ShutdownHandler;

/**
 * @coversDefaultClass \Drupal\KernelTests\KernelTestBase
 *
 * @group PHPUnit
 * @group Test
 * @group KernelTests
 */
class KernelTestBaseShutdownTest extends KernelTestBase {

  /**
   * Indicates which shutdown functions are expected to be called.
   *
   * @var array
   */
  protected $expectedShutdownCalled;

  /**
   * Indicates which shutdown functions have been called.
   *
   * @var array
   */
  protected static $shutdownCalled;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // Initialize static variable prior to testing.
    self::$shutdownCalled = [];
    parent::setUp();
  }

  /**
   * @covers ::assertPostConditions
   */
  public function testShutdownFunction() {
    $this->expectedShutdownCalled = ['shutdownFunction', 'shutdownFunction2'];
    ShutdownHandler::getInstance()->add([$this, 'shutdownFunction']);
  }

  /**
   * @covers ::assertPostConditions
   */
  public function testNoShutdownFunction() {
    $this->expectedShutdownCalled = [];
  }

  /**
   * Registers that this shutdown function has been called.
   */
  public function shutdownFunction() {
    self::$shutdownCalled[] = 'shutdownFunction';
    ShutdownHandler::getInstance()->add([$this, 'shutdownFunction2']);
  }

  /**
   * Registers that this shutdown function has been called.
   */
  public function shutdownFunction2() {
    self::$shutdownCalled[] = 'shutdownFunction2';
  }

  /**
   * {@inheritdoc}
   */
  protected function assertPostConditions(): void {
    parent::assertPostConditions();
    $this->assertSame($this->expectedShutdownCalled, self::$shutdownCalled);
  }

}
