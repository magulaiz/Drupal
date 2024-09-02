<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Kernel\Plugin;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the migration plugin manager.
 *
 * @coversDefaultClass \Drupal\migrate\Plugin\MigratePluginManager
 * @group migrate
 */
class MigrationPluginConfigurationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'migrate',
    'migrate_drupal',
    // Test with a simple migration.
    'ban',
    'locale',
    'field',
  ];

  /**
   * Tests merging configuration into a plugin through the plugin manager.
   *
   * @param array $new_configuration
   *   The migration plugin configuration.
   * @param array $expected_source
   *   The expected source plugin configuration.
   * @param array $expected_destination
   *   The expected destination plugin configuration.
   *
   * @dataProvider mergeProvider
   */
  public function testConfigurationMerge(array $new_configuration, array $expected_source, array $expected_destination): void {
    /** @var \Drupal\migrate\Plugin\MigrationInterface $migration */
    $migration = $this->container->get('plugin.manager.migration')
      ->createInstance('d7_blocked_ips', $new_configuration);
    $source_configuration = $migration->getSourceConfiguration();
    $this->assertEquals($expected_source, $source_configuration);
    $destination_configuration = $migration->getDestinationConfiguration();
    $this->assertEquals($expected_destination, $destination_configuration);
  }

  /**
   * Provide configuration data for testing.
   */
  public static function mergeProvider() {
    return [
      'Tests adding new configuration to a migration' => [
        'new_configuration' => [
          'source' => [
            'constants' => [
              'added_setting' => 'Ban them all!',
            ],
          ],
        ],
        'expected_source' => [
          'plugin' => 'd7_blocked_ips',
          'constants' => [
            'added_setting' => 'Ban them all!',
          ],
        ],
        'expected_destination' => [
          'plugin' => 'blocked_ip',
        ],
      ],
      'Tests overriding source configuration' => [
        'new_configuration' => [
          'source' => [
            'plugin' => 'a_different_plugin',
          ],
        ],
        'expected_source' => [
          'plugin' => 'a_different_plugin',
        ],
        'expected_destination' => [
          'plugin' => 'blocked_ip',
        ],
      ],
      'Tests overriding source and destination configuration' => [
        'new_configuration' => [
          'source' => [
            'plugin' => 'empty',
          ],
          'destination' => [
            'plugin' => 'entity:entity_view_mode',
          ],
        ],
        'expected_source' => [
          'plugin' => 'empty',
        ],
        'expected_destination' => [
          'plugin' => 'entity:entity_view_mode',
        ],
      ],
      'Test the source plugin is invalidated' => [
        'new_configuration' => [
          'source' => [
            'plugin' => 'embedded_data',
            'data_rows' => [],
            'ids' => [],
          ],
          'destination' => [
            'plugin' => 'entity:entity_view_mode',
          ],
        ],
        'expected_source' => [
          'plugin' => 'embedded_data',
          'data_rows' => [],
          'ids' => [],
        ],
        'expected_destination' => [
          'plugin' => 'entity:entity_view_mode',
        ],
      ],
      'Test the destination plugin is invalidated' => [
        'new_configuration' => [
          'source' => [
            'plugin' => 'embedded_data',
            'data_rows' => [],
            'ids' => [],
          ],
          'destination' => [
            'plugin' => 'null',
          ],
        ],
        'expected_source' => [
          'plugin' => 'embedded_data',
          'data_rows' => [],
          'ids' => [],
        ],
        'expected_destination' => [
          'plugin' => 'null',
        ],
      ],
      'Tests overriding source properties' => [
        'new_configuration' => [
          'source' => [
            'plugin' => 'variable',
            'variables' => [
              'locale_cache_strings',
              'locale_js_directory',
            ],
            'source_module' => 'locale',
          ],
        ],
        'expected_source' => [
          'plugin' => 'variable',
          'variables' => [
            'locale_cache_strings',
            'locale_js_directory',
          ],
          'source_module' => 'locale',
        ],
        'expected_destination' => [
          'plugin' => 'blocked_ip',
        ],
      ],
    ];
  }

}
