<?php

namespace Drupal\Tests\migrate\Kernel\Plugin\condition;

use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Plugin\migrate\condition\Callback;

/**
 * Tests the callback condition plugin.
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
    $plugin_definition = \Drupal::service('plugin.manager.migrate.condition')->getDefinition('callback');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The callable configuration is required when using the callback condition.');
    $condition = new Callback($configuration, 'callback', $plugin_definition);
  }

}
