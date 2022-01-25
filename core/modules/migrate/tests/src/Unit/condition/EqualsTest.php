<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\migrate\Plugin\migrate\condition\Equals;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\Equals
 * @group migrate
 */
class EqualsTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new Equals($configuration, 'equals', []);
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
        'message' => 'Exactly one of value and property must be set when using the equals condition.',
      ],
      [
        'configuration' => [],
        'message' => 'Exactly one of value and property must be set when using the equals condition.',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the equals condition.',
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
    $condition = new Equals($configuration, 'equals', []);
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
          'value' => 123,
        ],
        'source' => 123,
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => 123,
        ],
        'source' => 321,
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => NULL,
        ],
        'source' => FALSE,
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => NULL,
          'identical' => TRUE,
        ],
        'source' => FALSE,
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 2, 3],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'value' => [1, 2, 3],
        ],
        'source' => [1, 3, 2],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
        ],
        'source' => 45,
        'property_value' => '45',
        'expected' => TRUE,
      ],
      [
        'configuration' => [
          'property' => 'my_property',
          'identical' => TRUE,
        ],
        'source' => 45,
        'property_value' => '45',
        'expected' => FALSE,
      ],
    ];
  }

}
