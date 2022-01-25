<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\Plugin\migrate\condition\AllElements;
use Drupal\migrate\Plugin\migrate\condition\Equals;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the all_elements condition plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\AllElements
 */
class AllElementsTest extends UnitTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $configuration, $sub_evaluate_map, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();

    $map = [];
    foreach($sub_evaluate_map as $source_then_return) {
      $map[] = [$source_then_return[0], $row, $source_then_return[1]];
    }
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateConditionInterface')
      ->getMock();
    $condition->expects($this->any())
      ->method('evaluate')
      ->willReturnMap($map);
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $condition = new AllElements($configuration, 'all_elements', [], $condition_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 1,
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, TRUE],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 1,
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 1,
        'configuration' => [
          'condition' => 'foo',
          'negate' => TRUE,
        ],
        'sub_evaluate_map' => [
          [1, TRUE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, TRUE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, FALSE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
        ],
        'sub_evaluate_map' => [
          [1, TRUE],
          [2, TRUE],
          [3, TRUE],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
          'negate' => TRUE,
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, TRUE],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => [1, 2, 3],
        'configuration' => [
          'condition' => 'foo',
          'negate' => TRUE,
        ],
        'sub_evaluate_map' => [
          [1, FALSE],
          [2, FALSE],
          [3, FALSE],
        ],
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * @covers ::__construct
   */
  public function testConstructor() {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();

    $equals_configuration = ['value' => 123];
    $equals = new Equals($equals_configuration, 'equals', []);
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->willReturnMap([['equals', ['value' => 123], $equals]]);

    $configuration = [
      'condition' => 'equals',
      'configuration' => [
        'value' => 123,
      ],
    ];
    $condition = new AllElements($configuration, 'all_elements', [], $condition_manager);
    $this->assertSame(TRUE, $condition->evaluate([123, 123], $row));
    $this->assertSame(FALSE, $condition->evaluate([123, 789], $row));
  }

}
