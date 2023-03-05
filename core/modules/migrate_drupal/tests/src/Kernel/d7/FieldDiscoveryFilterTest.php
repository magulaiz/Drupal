<?php

namespace Drupal\Tests\migrate_drupal\Kernel\d7;

use Drupal\Tests\migrate_drupal\Kernel\MigrateDrupalTestBase;

// cspell:ignore entityreference filefield imagefield optionwidgets

/**
 * Tests FieldDiscovery filtering against Drupal 7.
 *
 * @group migrate_drupal
 * @coversDefaultClass \Drupal\migrate_drupal\FieldDiscovery
 */
class FieldDiscoveryFilterTest extends MigrateDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'filter',
    'image',
    'link',
    'node',
    'options',
    'telephone',
    'text',
  ];

  /**
   * The field plugin manager.
   *
   * @var \Drupal\migrate_drupal\Plugin\MigrateFieldPluginManagerInterface
   */
  protected $fieldPluginManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('user', ['users_data']);
    $this->fieldPluginManager = $this->container->get('plugin.manager.migrate.field');
  }

  /**
   * Tests the getDefinitions method.
   *
   * @covers ::getDefinitions
   */
  public function testGetDefinitions() {
    $definitions = $this->fieldPluginManager->getDefinitions();
    $expected = [
      'd6_text',
      'd7_text',
      'datetime',
      'email',
      'entityreference',
      'file',
      'filefield',
      'image',
      'imagefield',
      'link',
      'link_field',
      'list',
      'node_reference',
      'nodereference',
      'number_default',
      'options',
      'optionwidgets',
      'phone',
      'taxonomy_term_reference',
      'telephone',
      'user_reference',
      'userreference',
    ];
    $this->assertSame($expected, array_keys($definitions));

    // Confirm that a field plugin for an uninstalled module is not returned.
    \Drupal::service('module_installer')->uninstall(['link']);
    unset($expected[9]);
    unset($expected[10]);
    sort($expected);
    $definitions = $this->fieldPluginManager->getDefinitions();
    $this->assertSame($expected, array_keys($definitions));
  }

}
