<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\Plugin\migrate\process\IfCondition;

/**
 * Tests the if_condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\IfCondition
 */
class IfConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestIfCondition
   */
  public function testIfCondition($source, $evaluate, $negate, $expected, $do_get = [], $else_get = []) {
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

    $map = [];
    if (!empty($do_get)) {
      $map[] = [$do_get['property'], $do_get['value']];
    }
    if (!empty($else_get)) {
      $map[] = [$else_get['property'], $else_get['value']];
    }
    if (!empty($map)) {
      $this->row
        ->method('get')
        ->willReturnMap($map);
    }

    $configuration = [
      'condition' => 'foo',
      'negate' => $negate,
    ];
    if (!empty($do_get)) {
      $configuration['do_get'] = $do_get['property'];
    }
    if (!empty($else_get)) {
      $configuration['else_get'] = $else_get['property'];
    }
    $evaluated = (new IfCondition($configuration, 'if_condition', [], $condition_manager))
      ->transform($source, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $evaluated);
  }

  /**
   * Data provider for ::testIfCondition().
   */
  public function providerTestIfCondition() {
    return [
      [
        'source' => 123,
        'evaluate' => TRUE,
        'negate' => FALSE,
        'expected' => 123,
      ],
      [
        'source' => 123,
        'evaluate' => TRUE,
        'negate' => TRUE,
        'expected' => NULL,
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'negate' => FALSE,
        'expected' => NULL,
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'negate' => TRUE,
        'expected' => 123,
      ],
      [
        'source' => 123,
        'evaluate' => TRUE,
        'negate' => FALSE,
        'expected' => 'my do',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
      [
        'source' => 123,
        'evaluate' => TRUE,
        'negate' => TRUE,
        'expected' => 'else value',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'negate' => FALSE,
        'expected' => 'else value',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
      [
        'source' => 123,
        'evaluate' => FALSE,
        'negate' => TRUE,
        'expected' => 'my do',
        'do_get' => [
          'property' => 'some do get',
          'value' => 'my do',
        ],
        'else_get' => [
          'property' => 'else get property',
          'value' => 'else value',
        ],
      ],
    ];
  }

}
