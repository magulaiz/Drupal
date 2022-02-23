<?php

namespace Drupal\Tests\ckeditor5\Kernel;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Source Editing plugin.
 *
 * @group ckeditor5
 * @internal
 */
class SourceEditingTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ckeditor5',
    'filter',
    'editor',
  ];

  /**
   * The manager for "CKEditor 5 plugin" plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.ckeditor5.plugin');
  }

  /**
   * Tests GHS configuration for source editing.
   */
  public function testGhsConfiguration() {
    $this->manager->getPlugin('ckeditor5_sourceEditing')
  }

  public function providerGhsConfiguration() {
    return [
      []
    ];
  }

}
