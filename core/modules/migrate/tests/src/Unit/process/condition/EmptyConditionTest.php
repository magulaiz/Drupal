<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\Plugin\migrate\process\condition\EmptyCondition;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\EmptyCondition
 * @group migrate
 */
class EmptyConditionTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $expected) {
    $condition = new EmptyCondition($configuration, 'empty', []);
    $this->assertSame($expected, $condition->evaluate($source));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'configuration' => [],
        'source' => 'string',
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => 123,
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => [1, 2, 3],
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => TRUE,
        'expected' => FALSE,
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
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => FALSE,
        'expected' => TRUE,
      ],
    ];
  }

}
