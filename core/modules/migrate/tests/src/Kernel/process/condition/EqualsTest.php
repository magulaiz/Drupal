<?php

namespace Drupal\Tests\migrate\Kernel\process\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Plugin\migrate\process\condition\Equals;

/**
 * Tests the equals process condition plugin.
 *
 * @group migrate
 */
class EqualsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate.process_condition')->getDefinition('equals');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The value configuration is required when using the equals process condition.');
    $condition = new Equals($configuration, 'equals', $plugin_definition);
  }

}
