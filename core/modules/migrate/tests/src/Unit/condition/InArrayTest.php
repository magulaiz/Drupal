<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\migrate\Plugin\migrate\condition\InArray;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\InArray
 * @group migrate
 */
class InArrayTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new InArray($configuration, 'in_array', []);
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
        'message' => 'Exactly one of value and property must be set when using the in_array condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the in_array condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the in_array condition.',
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
    $condition = new InArray($configuration, 'in_array', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
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
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => 'string',
        ],
        'source' => 'something else',
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => 2,
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => 4,
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 3, 3],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, [1, 2, 3]],
        ],
        'source' => [1, 2, 3],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 2,
        'property_value' => [1, 2, 3],
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 4,
        'property_value' => [1, 2, 3],
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 2,
        'property_value' => 2,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => '2',
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
          'strict' => TRUE,
        ],
        'source' => '2',
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 'something'],
        ],
        'source' => 'something',
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => ['key' => 'something'],
        ],
        'source' => 'key',
        'property_value' => NULL,
        'expected' => FALSE,
      ],
    ];
  }

}
