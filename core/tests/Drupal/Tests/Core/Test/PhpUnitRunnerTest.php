<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Test;

use Drupal\Core\Test\SimpletestTestRunResultsStorage;
use Drupal\Core\Test\TestRun;
use Drupal\Core\Test\TestStatus;
use Drupal\TestTools\PhpUnitRunner;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\TestTools\PhpUnitRunner
 * @group Test
 *
 * @see Drupal\Tests\simpletest\Unit\SimpletestPhpunitRunCommandTest
 */
class PhpUnitRunnerTest extends UnitTestCase {

  /**
   * Tests an error in the test running phase.
   *
   * @covers ::runOneTestClass
   */
  public function testRunOneTestClassError(): void {
    $test_id = 23;
    $log_path = 'test_log_path';

    // Create a mock test run storage.
    $storage = $this->getMockBuilder(SimpletestTestRunResultsStorage::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['createNew'])
      ->getMock();

    // Set some expectations for createNew().
    $storage->expects($this->once())
      ->method('createNew')
      ->willReturn($test_id);

    // Create a mock runner.
    $runner = $this->getMockBuilder(PhpUnitRunner::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['runPhpUnit'])
      ->getMock();

    // We mark a failure by having runPhpUnit() deliver a serious status code.
    $runner->expects($this->once())
      ->method('runPhpUnit')
      ->willReturnCallback(
        function (array $command, array $processEnvironmentVariables, ?string &$output = NULL, ?string &$error = NULL): int {
          return TestStatus::EXCEPTION;
        }
      );

    // Actually execute the test in the partially mocked object.
    $test_run = TestRun::createNew($storage, 'SomeTest');
    $status = $runner->runOneTestClass($test_run);

    // Make sure our status code made the round trip.
    $this->assertEquals(TestStatus::EXCEPTION, $status);

    // A serious error in runCommand() should give us a fixed set of results.
    $results = $test_run->getResults();
    $row = reset($results);
    $fail_row = [
      'test_id' => $test_id,
      'test_class' => 'SomeTest',
      'status' => TestStatus::label(TestStatus::EXCEPTION),
      'message' => 'PHPUnit Test failed to complete; Error: ',
      'message_group' => 'Other',
      'function' => 'SomeTest',
      'line' => '0',
      'file' => $log_path,
    ];
    $this->assertEquals($fail_row, $row);
  }

  /**
   * @covers ::phpUnitCommand
   */
  public function testPhpUnitCommand(): void {
    $runner = new PhpUnitRunner($this->root, sys_get_temp_dir());
    $invokableMethod = new \ReflectionMethod($runner, 'phpUnitCommand');
    $this->assertMatchesRegularExpression('/phpunit/', $invokableMethod->invoke($runner));
  }

}
