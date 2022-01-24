<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\Plugin\migrate\process\FirstMeetingCondition;

/**
 * Tests the first_meeting_condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\FirstMeetingCondition
 */
class FirstMeetingConditionTest extends MigrateProcessTestCase {

  /**
   * Tests configuration validation in constructor.
   */
  public function testSkipOnConditionConstructor() {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface')
      ->getMock();
    $process_condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $process_condition_manager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [];
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "condition" must be set.');
    (new FirstMeetingCondition($configuration, 'first_meeting_condition', [], $process_condition_manager))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestFirstMeetingCondition
   */
  public function testFirstMeetingCondition($value, $evaluate, $negate, $default_value, $expected) {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface')
      ->getMock();
    $condition->expects($this->exactly(count($evaluate)))
      ->method('evaluate')
      ->willReturnOnConsecutiveCalls(...$evaluate);
    $process_condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $process_condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'foo',
      'negate' => $negate,
      'default_value' => $default_value,
    ];
    $transformed = (new FirstMeetingCondition($configuration, 'first_meeting_condition', [], $process_condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $transformed);
  }

  /**
   * Data provider for ::testFirstMeetingCondition().
   */
  public function providerTestFirstMeetingCondition() {
    return [
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'one',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, TRUE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'two',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, TRUE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'three',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, FALSE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'my default',
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE],
        'negate' => TRUE,
        'default_value' => 'my default',
        'expected' => 'one',
      ],
      [
        'value' => 'one',
        'evaluate' => [TRUE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'one',
      ],
      [
        'value' => 'one',
        'evaluate' => [FALSE],
        'negate' => FALSE,
        'default_value' => 'my default',
        'expected' => 'my default',
      ],
    ];
  }

}
