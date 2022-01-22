<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\Plugin\migrate\process\condition\Callback;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\Callback
 * @group migrate
 */
class CallbackTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $condition = new Callback($configuration, 'callback', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'configuration' => [
          'callable' => 'is_null',
        ],
        'source' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'callable' => 'is_null',
        ],
        'source' => 'something',
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'source' => [
          'my string',
          's',
        ],
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'source' => [
          'my string',
          'x',
        ],
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'callable' => 'strpos',
          'unpack_source' => TRUE,
        ],
        'source' => [
          'my string',
          'm',
        ],
        'expected' => FALSE,
      ],
    ];
  }

}
