<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\condition\OlderThan;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\OlderThan
 * @group migrate
 */
class OlderThanTest extends UnitTestCase {

  /**
   * @covers ::__construct()
   * @dataProvider providerTestConfigurationValidation
   */
  public function testConfigurationValidation($configuration, $message) {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $condition = new OlderThan($configuration, 'older_than', []);
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
          'format' => 'U',
        ],
        'message' => 'Exactly one of value and property must be set when using the older_than condition.',
      ],
      [
        'configuration' => [
          'format' => 'U',
        ],
        'message' => 'Exactly one of value and property must be set when using the older_than condition',
      ],
      [
        'configuration' => [
          'property' => 123,
          'format' => 'U',
        ],
        'message' => 'The property configuration must be a string when using the older_than condition.',
      ],
      [
        'configuration' => [
          'value' => 'not a date',
          'format' => 'U',
        ],
        'message' => "The 'value' passed to older_than could not be converted into a datetime object.",
      ],
      [
        'configuration' => [
          'value' => '1 1 2022',
          'format' => 'U',
        ],
        'message' => "The 'value' passed to older_than could not be converted into a datetime object.",
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
    $condition = new OlderThan($configuration, 'older_than', []);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    return [
      [
        'source' => '1642973916',
        'configuration' => [
          'value' => '1642973915',
          'format' => 'U',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => '1642973915',
        'configuration' => [
          'value' => '1642973916',
          'format' => 'U',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'value' => '24 Jan 2022',
          'format' => 'n j Y',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'value' => '24 Jan 2022',
          'format' => 'j n Y',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'value' => '1 4 2022',
          'format' => 'j n Y',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'value' => '+1 year',
          'format' => 'U',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'value' => '-1 second',
          'format' => 'U',
        ],
        'property_value' => NULL,
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'value' => '-100 years',
          'format' => 'U',
        ],
        'property_value' => NULL,
        'expected' => FALSE,
      ],
      // Property used below.
      [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '1642973915',
        'expected' => FALSE,
      ],
      [
        'source' => '1642973915',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '1642973916',
        'expected' => TRUE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'n j Y',
        ],
        'property_value' => '24 Jan 2022',
        'expected' => TRUE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'j n Y',
        ],
        'property_value' => '24 Jan 2022',
        'expected' => FALSE,
      ],
      [
        'source' => '1 2 2022',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'j n Y',
        ],
        'property_value' => '1 4 2022',
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '+1 year',
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '-1 second',
        'expected' => TRUE,
      ],
      [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '-100 years',
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
  public function testEvaluateExceptions($source, $configuration, $property_value, $expected_message) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($configuration['property'])) {
      $row->expects($this->any())
        ->method('get')
        ->willReturn($property_value);
    }
    $condition = new OlderThan($configuration, 'older_than', []);
    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage($expected_message);
    $condition->evaluate($source, $row);
  }

  /**
   * Data provider for ::testEvaluateExceptions().
   */
  public function providerTestEvaluateExceptions() {
    return [
      'Property not datetime string' => [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => 'not a datetime string',
        'expected_message' => "The 'property' passed to older_than could not be converted into a datetime object.",
      ],
      'Property datetime string but wrong format' => [
        'source' => '1642973916',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '1 4 2022',
        'expected_message' => "The 'property' passed to older_than could not be converted into a datetime object.",
      ],
      'Invalid source' => [
        'source' => '1 4 2022',
        'configuration' => [
          'property' => 'whatever',
          'format' => 'U',
        ],
        'property_value' => '-1 day',
        'expected_message' => 'The date cannot be created from a format.',
      ],
    ];
  }

}
