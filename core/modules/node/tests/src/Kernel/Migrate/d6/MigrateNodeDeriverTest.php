<?php

namespace Drupal\Tests\node\Kernel\Migrate\d6;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Tests\migrate_drupal\Kernel\d6\MigrateDrupal6TestBase;

/**
 * Test D6NodeDeriver.
 *
 * @group migrate_drupal_6
 */
class MigrateNodeDeriverTest extends MigrateDrupal6TestBase {
  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->pluginManager = $this->container->get('plugin.manager.migration');
  }

  /**
   * Tests node translation migrations with translation disabled.
   */
  public function testNoTranslations() {
    // Without content_translation, there should be no translation migrations.
    $this->expectException(PluginNotFoundException::class);
    $this->pluginManager->createInstances('d6_node_translation');
  }

  /**
   * Tests node translation migrations with translation enabled.
   */
  public function testTranslations() {
    // With content_translation, there should be translation migrations for
    // each content type.
    $this->enableModules(['language', 'content_translation']);
    $this->assertTrue($this->container->get('plugin.manager.migration')->hasDefinition('d6_node_translation:story'), "Node translation migrations exist after content_translation installed");
  }

}
