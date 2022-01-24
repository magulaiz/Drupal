<?php

namespace Drupal\Tests\migrate\Unit\process\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\process\condition\Contains;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\condition\Contains
 * @group migrate
 */
class ContainsTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new Contains($configuration, 'contains', []);
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
        'message' => 'Exactly one of value and property must be set when using the contains process condition.',
      ],
      [
        'configuration' => [
          'format' => 'U',
        ],
        'message' => 'Exactly one of value and property must be set when using the contains process condition',
      ],
      [
        'configuration' => [
          'property' => 123,
        ],
        'message' => 'The property configuration must be a string when using the contains process condition.',
      ],
    ];
  }

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $configuration, $property_value, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->willReturn($property_value);
    }
    $condition = new Contains($configuration, 'contains', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => 'my string',
        'configuration' => [
          'value' => 'str',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'value' => 'trs',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'str',
        'expected' => TRUE,
      ],
      [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'trs',
        'expected' => FALSE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'value' => 'two',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'value' => 'four',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'two',
        'expected' => TRUE,
      ],
      [
        'source' => ['one', 'two', 'three'],
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 'four',
        'expected' => FALSE,
      ],
    ];
  }

  /**
   * @covers ::evaluate
   * @dataProvider providerTestEvaluateExceptions
   */
  public function testEvaluateExceptions($source, $configuration, $property_value, $expected_message) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->willReturn($property_value);
    }
    $condition = new Contains($configuration, 'contains', []);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage($expected_message);
    $condition->evaluate($source, $row);
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      'Source string but value not a string.' => [
        'source' => 'my string',
        'configuration' => [
          'value' => 123,
        ],
        'property_value' => NULL,
        'expected' => 'When using the contains condition with a string source, the value/property must be a string.',
      ],
      'Source string but property not a string.' => [
        'source' => 'my string',
        'configuration' => [
          'property' => 'whatever',
        ],
        'property_value' => 123,
        'expected' => 'When using the contains condition with a string source, the value/property must be a string.',
      ],
      'Source is neither array nor string' => [
        'source' => 123,
        'configuration' => [
          'value' => 'whatever',
        ],
        'property_value' => NULL,
        'expected' => 'When using the contains condition the source must be an array or a string.',
      ],
    ];
  }

}
