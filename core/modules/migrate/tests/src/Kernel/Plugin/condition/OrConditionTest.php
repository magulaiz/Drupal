<?php

namespace Drupal\Tests\migrate\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the or condition plugin.
 *
 * @group migrate
 */
class OrConditionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests evaluating the or condition.
   */
  public function testEvaluate() {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $configuration = [
      [
        'condition' => 'in_array',
        'configuration' => [
          'value' => [1, 2, 3],
        ],
      ],
      [
        'condition' => 'greater_than',
        'negate' => TRUE,
        'configuration' => [
          'value' => 2,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate.condition')->createInstance('or', $configuration);

    $this->assertTrue($condition->evaluate(0, $row));
    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertTrue($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));
  }

}
