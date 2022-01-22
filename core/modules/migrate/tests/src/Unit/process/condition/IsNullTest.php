<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\Plugin\migrate\process\condition\IsNull;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\IsNull
 * @group migrate
 */
class IsNullTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $condition = new IsNull($configuration, 'empty', []);
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
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => '',
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => [],
        'expected' => FALSE,
      ],
      [
        'configuration' => [],
        'source' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [],
        'source' => FALSE,
        'expected' => FALSE,
      ],
    ];
  }

}
