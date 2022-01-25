<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\process\FilterOnCondition;

/**
 * Tests the filter_on_condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\FilterOnCondition
 */
class FilterOnConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestFilterOnCondition
   */
  public function testFilterOnCondition($value, $evaluate, $negate, $expected, $preserve_keys = NULL) {
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
    ];
    if (!is_null($preserve_keys)) {
      $configuration['preserve_keys'] = $preserve_keys;
    }
    $transformed = (new FilterOnCondition($configuration, 'filter_on_condition', [], $process_condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $transformed);
  }

  /**
   * Data provider for ::testFilterOnCondition().
   */
  public function providerTestFilterOnCondition() {
    return [
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, TRUE, TRUE],
        'negate' => FALSE,
        'expected' => ['one', 'two', 'three'],
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'negate' => FALSE,
        'expected' => [0 => 'one', 1 => 'three'],
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'negate' => FALSE,
        'expected' => [0 => 'one', 1 => 'three'],
        'preserve_keys' => FALSE,
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, TRUE],
        'negate' => FALSE,
        'expected' => [0 => 'one', 2 => 'three'],
        'preserve_keys' => TRUE,
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [FALSE, FALSE, FALSE],
        'negate' => FALSE,
        'expected' => [],
      ],
      [
        'value' => ['one', 'two', 'three'],
        'evaluate' => [TRUE, FALSE, FALSE],
        'negate' => TRUE,
        'expected' => ['two', 'three'],
      ],
    ];
  }

  /**
   * Tests input validation.
   */
  public function testFilterOnConditionNotArray() {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface')
      ->getMock();
    $process_condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $process_condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $configuration = [
      'condition' => 'foo',
    ];
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage('The input value should be an array.');
    (new FilterOnCondition($configuration, 'filter_on_condition', [], $process_condition_manager))
      ->transform('', $this->migrateExecutable, $this->row, 'destination_property');
  }

}
