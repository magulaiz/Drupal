<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\Plugin\migrate\process\EvaluateCondition;

/**
 * Tests the evaluate_condition process plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\EvaluateCondition
 */
class EvaluateConditionTest extends MigrateProcessTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluateCondition
   */
  public function testEvaluateCondition($evaluate, $negate, $expected) {
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
      'condition' => 'foo',
      'negate' => $negate,
    ];
    $evaluated = (new EvaluateCondition($configuration, 'evaluate_condition', [], $condition_manager))
      ->transform($value, $this->migrateExecutable, $this->row, 'destination_property');
    $this->assertSame($expected, $evaluated);
  }

  /**
   * Data provider for ::testEvaluateCondition().
   */
  public function providerTestEvaluateCondition() {
    return [
      'true not negated' => [
        'evaluate' => TRUE,
        'negate' => FALSE,
        'expected' => TRUE,
      ],
      'true negated' => [
        'evaluate' => TRUE,
        'negate' => TRUE,
        'expected' => FALSE,
      ],
      'false not negated' => [
        'evaluate' => FALSE,
        'negate' => FALSE,
        'expected' => FALSE,
      ],
      'false negated' => [
        'evaluate' => FALSE,
        'negate' => TRUE,
        'expected' => TRUE,
      ],
    ];
  }

}
