<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\process\condition\Callback;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\Callback
 * @group migrate
 */
class CallbackTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new Callback($configuration, 'callback', []);
  }

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'callable' => 123,
        ],
        'message' => 'The "callable" must be a valid function or method.',
      ],
    ];
  }

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

  /**
   * Test that MigrateExceptions are thrown when dynamic dates are invalid.
   *
   * @covers ::evaluate
   * @dataProvider providerTestEvaluateExceptions
   */
  public function testEvaluateExceptions($source, $configuration, $expected_message) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $condition = new Callback($configuration, 'callback', []);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage($expected_message);
    $condition->evaluate($source, $row);
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      [
        'source' => 'not an array',
        'configuration' => [
          'callable' => 'str_replace',
          'unpack_source' => TRUE,
        ],
        'expected_message' => "When 'unpack_source' is set, the source must be an array. Instead it was of type 'string'",
      ],
    ];
  }

}
