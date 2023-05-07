<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\migrate\process\SkipOnCondition;

/**
 * Tests the skip on condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\SkipOnCondition
 */
class SkipOnConditionTest extends MigrateProcessTestCase {

  /**
   * Tests configuration validation in constructor.
   *
   * @dataProvider providerTestConstructorValidation
   */
  public function testConstructorValidation($configuration, $message) {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateConditionInterface')
      ->getMock();
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $process = new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      'no condition' => [
        'configuration' => [
          'method' => 'row',
        ],
        'message' => 'The "condition" must be set.',
      ],
      'string config' => [
        'configuration' => [
          'method' => 'row',
          'condition' => 'foo',
          'configuration' => 'some string',
        ],
        'message' => 'If "configuration" is set it must be an array.',
      ],
      'no method' => [
        'configuration' => [
          'condition' => 'foo',
        ],
        'message' => 'The "method" must be set to either "row" or "process".',
      ],
      'bad method' => [
        'configuration' => [
          'method' => 'invalid',
          'condition' => 'foo',
        ],
        'message' => 'The "method" must be set to either "row" or "process".',
      ],
    ];
  }

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestSkipOnCondition
   */
  public function testSkipOnCondition($will_skip, $method, $evaluate, $negate, $message) {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateConditionInterface')
      ->getMock();
    $condition->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue($evaluate));
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $value = 123;
    $configuration = [
      'method' => $method,
      'condition' => 'foo',
      'negate' => $negate,
      'message' => $message,
    ];
    if ($will_skip) {
      if ($method === 'process') {
        $this->expectException(MigrateSkipProcessException::class);
      }
      else {
        $this->expectException(MigrateSkipRowException::class);
        $this->expectExceptionMessage($message);
      }
      (new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager))
        ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    }
    else {
      $pass_through = (new SkipOnCondition($configuration, 'skip_on_condition', [], $condition_manager))
        ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
      $this->assertSame($value, $pass_through);
    }
  }

  /**
   * Data provider for ::testSkipOnCondition().
   */
  public function providerTestSkipOnCondition() {
    return [
      'skip row no message' => [
        'will_skip' => TRUE,
        'method' => 'row',
        'evaluate' => TRUE,
        'negate' => FALSE,
        'message' => '',
      ],
      'skip row with message' => [
        'will_skip' => TRUE,
        'method' => 'row',
        'evaluate' => TRUE,
        'negate' => FALSE,
        'message' => 'My message',
      ],
      'skip row using negate' => [
        'will_skip' => TRUE,
        'method' => 'row',
        'evaluate' => FALSE,
        'negate' => TRUE,
        'message' => '',
      ],
      'skip process' => [
        'will_skip' => TRUE,
        'method' => 'process',
        'evaluate' => TRUE,
        'negate' => FALSE,
        'message' => '',
      ],
      'skip process using negate' => [
        'will_skip' => TRUE,
        'method' => 'process',
        'evaluate' => FALSE,
        'negate' => TRUE,
        'message' => '',
      ],
      'pass through row' => [
        'will_skip' => FALSE,
        'method' => 'row',
        'evaluate' => FALSE,
        'negate' => FALSE,
        'message' => '',
      ],
      'pass through row by negation' => [
        'will_skip' => FALSE,
        'method' => 'row',
        'evaluate' => TRUE,
        'negate' => TRUE,
        'message' => '',
      ],
      'pass through process' => [
        'will_skip' => FALSE,
        'method' => 'process',
        'evaluate' => FALSE,
        'negate' => FALSE,
        'message' => '',
      ],
      'pass through process by negation' => [
        'will_skip' => FALSE,
        'method' => 'process',
        'evaluate' => TRUE,
        'negate' => TRUE,
        'message' => '',
      ],
    ];
  }

}
