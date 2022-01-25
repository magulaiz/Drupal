<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\migrate\Plugin\migrate\condition\IssetCondition;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\IssetCondition
 * @group migrate
 */
class IssetConditionTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $condition = new IssetCondition($configuration, 'isset', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'configuration' => [],
        'source' => 'string',
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => 123,
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => [1, 2, 3],
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => TRUE,
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => 0,
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => '',
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => [],
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => FALSE,
        'expected' => TRUE,
      ],
    ];
  }

}
