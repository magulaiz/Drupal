<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\Plugin\migrate\process\condition\GreaterThan;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\GreaterThan
 * @group migrate
 */
class GreaterThanTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new GreaterThan($configuration, 'greater_than', []);
  }

  /**
   * Data provider for ::testConfigurationValidation().
   */
  public function providerTestConfigurationValidation() {
    return [
      [
        'configuration' => [
          'value' => 5,
          'property' => 'my_property',
        ],
        'message' => 'Exactly one of value and property must be set when using the greater_than process condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the greater_than process condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the greater_than process condition.',
      ],
    ];
  }

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($configuration, $source, $property_value, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->willReturn($property_value);
    }
    $condition = new GreaterThan($configuration, 'greater_than', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'configuration' => [
          'value' => 'aaa',
        ],
        'source' => 'aaa',
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => 'aaa',
        ],
        'source' => 'bbb',
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => 'bbb',
        ],
        'source' => 'aaa',
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => 123,
        ],
        'source' => 123,
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => 123,
        ],
        'source' => 1230,
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 2, 3],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3, 4],
        ],
        'source' => [1, 2, 3],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 2, 3, 4],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 5],
        ],
        'source' => ['key' => 6],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 6],
        ],
        'source' => ['key' => 5],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 6],
        ],
        'source' => ['another_key' => 5],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 5],
        ],
        'source' => ['another_key' => 6],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => NULL,
        ],
        'source' => FALSE,
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 45,
        'property_value' => 46,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 46,
        'property_value' => 45,
        'expected' => TRUE,
      ],
    ];
  }

}
