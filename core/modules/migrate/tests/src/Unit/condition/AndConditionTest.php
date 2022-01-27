<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\migrate\Plugin\migrate\condition\AndCondition;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the and condition plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\AndCondition
 */
class AndConditionTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->willReturn(NULL);
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new AndCondition($configuration, 'and', [], $condition_manager);
  }

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [],
        'message' => 'The and condition requires configuration.',
      ],
      [
        'configuration' => [
          'key' => 'value',
        ],
        'message' => "The 'configuration' passed to the and condition must be an array or arrays.",
      ],
      [
        'configuration' => [
          [
            'key' => 'value',
          ],
        ],
        'message' => "Each configuration element passed to the and condition must have the `condition` set.",
      ],
      [
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'key' => 'value',
          ],
        ],
        'message' => "Each configuration element passed to the and condition must have the `condition` set.",
      ],
    ];
  }

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $evaluates, $configuration, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();

    $conditions = [];
    for ($i = 0; $i < count($configuration); $i++) {
      $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateConditionInterface')
        ->getMock();
      $condition->expects($this->any())
        ->method('evaluate')
        ->willReturn($evaluates[$i]);
      $conditions[] = $condition;
    }

    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->exactly(count($configuration)))
      ->method('createInstance')
      ->willReturnOnConsecutiveCalls(...$conditions);

    $condition = new AndCondition($configuration, 'and', [], $condition_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          TRUE,
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          FALSE,
          FALSE,
          FALSE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
            'negate' => TRUE,
          ],
          [
            'condition' => 'foo',
            'negate' => TRUE,
          ],
          [
            'condition' => 'foo',
            'negate' => TRUE,
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          FALSE,
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          FALSE,
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
            'negate' => TRUE,
          ],
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
          TRUE,
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
            'negate' => TRUE,
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          FALSE,
          TRUE,
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          TRUE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => TRUE,
      ],
      [
        'source' => 123,
        'evaluates' => [
          FALSE,
        ],
        'configuration' => [
          [
            'condition' => 'foo',
          ],
        ],
        'expected' => FALSE,
      ],
    ];
  }

}
