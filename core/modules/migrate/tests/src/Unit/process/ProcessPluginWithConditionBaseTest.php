<?php

namespace Drupal\Tests\migrate\Unit\process;

use Drupal\migrate\Plugin\migrate\condition\Equals;
use Drupal\migrate\Plugin\migrate\process\ProcessPluginWithConditionBase;

/**
 * Tests the constructor of the ProcessPluginWithConditionBase.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\process\ProcessPluginWithConditionBase
 */
class ProcessPluginWithConditionBaseTest extends MigrateProcessTestCase {

  /**
   * Tests configuration validation in constructor.
   *
   * @dataProvider providerTestConstructorValidation
   */
  public function testConstructorValidation($configuration, $message) {
    $condition = $this->getMockBuilder('\Drupal\migrate\Plugin\MigrateConditionInterface')
      ->getMock();
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnValue($condition));

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage($message);
    $this->getMockForAbstractClass(ProcessPluginWithConditionBase::class, [$configuration, 'just_a_base', [], $condition_manager]);
  }

  /**
   * Data provider for ::testConstructorValidation().
   */
  public function providerTestConstructorValidation() {
    return [
      'no condition' => [
        'configuration' => [],
        'message' => 'The "condition" must be set.',
      ],
      'string config' => [
        'configuration' => [
          'condition' => 'foo',
          'configuration' => 'some string',
        ],
        'message' => 'If "configuration" is set it must be an array.',
      ],
    ];
  }

  /**
   * Tests condition instance created by process constructor.
   */
  public function testConditionInstance() {
    $equals_configuration = ['value' => 123];
    $equals = new Equals($equals_configuration, 'equals', []);
    $condition_manager = $this->getMockBuilder('\Drupal\Component\Plugin\PluginManagerInterface')
      ->getMock();
    $condition_manager->expects($this->once())
      ->method('createInstance')
      ->willReturnMap([['equals', ['value' => 123], $equals]]);

    $configuration = [
      'condition' => 'equals',
      'configuration' => [
        'value' => 123,
      ],
    ];
    $process = new ProcessPluginWithConditionBaseTestClass($configuration, 'test', [], $condition_manager);
    $condition = $process->getCondition();
    $this->assertSame('equals', $condition->getPluginId());
    $this->assertTrue($condition->evaluate(123, $this->row));
    $this->assertFalse($condition->evaluate(321, $this->row));
  }

}

/**
 * A test class so we can get a protected property.
 */
class ProcessPluginWithConditionBaseTestClass extends ProcessPluginWithConditionBase {

  /**
   * Helper function to get a protected property.
   */
  public function getCondition() {
    return $this->condition;
  }

}
