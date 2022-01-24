<?php

namespace Drupal\Tests\migrate\Kernel\process\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Plugin\migrate\process\condition\OlderThan;

/**
 * Tests the older_than process condition plugin.
 *
 * @group migrate
 */
class OlderThanTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate.process_condition')->getDefinition('older_than');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The format configuration is required when using the older_than process condition.');
    $condition = new OlderThan($configuration, 'older_than', $plugin_definition);
  }

}
