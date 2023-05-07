<?php

namespace Drupal\Tests\migrate\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the and condition plugin.
 *
 * @group migrate
 */
class AndConditionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests evaluating the and condition.
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
        'condition' => 'equals',
        'negate' => TRUE,
        'configuration' => [
          'value' => 3,
        ],
      ],
    ];
    $condition = \Drupal::service('plugin.manager.migrate.condition')->createInstance('and', $configuration);

    $this->assertTrue($condition->evaluate(1, $row));
    $this->assertTrue($condition->evaluate(2, $row));
    $this->assertFalse($condition->evaluate(3, $row));
    $this->assertFalse($condition->evaluate(4, $row));
  }

}
