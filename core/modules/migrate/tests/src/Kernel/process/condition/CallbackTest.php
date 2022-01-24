<?php

namespace Drupal\Tests\migrate\Kernel\process\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Plugin\migrate\process\condition\Callback;

/**
 * Tests the callback process condition plugin.
 *
 * @group migrate
 */
class CallbackTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['migrate'];

  /**
   * Tests validation in constructor.
   */
  public function testConstructor() {
    $configuration = [];
    $plugin_definition = \Drupal::service('plugin.manager.migrate.process_condition')->getDefinition('callback');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The callable configuration is required when using the callback process condition.');
    $condition = new Callback($configuration, 'callback', $plugin_definition);
  }

}
