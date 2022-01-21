<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\Plugin\migrate\process\condition\Equals;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\EmptyCondition
 * @group migrate
 */
class EqualsTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $expected) {
    $condition = new Equals($configuration, 'equals', []);
    $this->assertSame($expected, $condition->evaluate($source));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'configuration' => [
          'value' => 'string',
        ],
        'source' => 'string',
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => 'string',
        ],
        'source' => 'something else',
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => 123,
        ],
        'source' => 123,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => 123,
        ],
        'source' => 321,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => NULL,
        ],
        'source' => FALSE,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => NULL,
          'identical' => TRUE,
        ],
        'source' => FALSE,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 2, 3],
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 3, 2],
        'expected' => FALSE,
      ],
    ];
  }

}
