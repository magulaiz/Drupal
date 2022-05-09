<?php

namespace Drupal\Tests\field\Unit\Plugin\migrate\process\d6;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\field\Plugin\migrate\process\d6\FieldSettings;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\MigrateFieldPluginManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\field\Plugin\migrate\process\d6\FieldSettings
 * @group field
 */
class FieldSettingsTest extends UnitTestCase {

  /**
   * @covers ::getSettings
   *
   * @dataProvider getSettingsProvider
   */
  public function testGetSettings($field_type, $field_settings, $source_field_type, $allowed_values) {
    $plugin_manager = $this->getMockBuilder(MigrateFieldPluginManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $plugin_manager->method('createInstance')
      ->willThrowException(new PluginNotFoundException($source_field_type));

    $plugin = new FieldSettings([], 'd6_field_settings', [], $plugin_manager);

    $executable = $this->createMock(MigrateExecutableInterface::class);
    $row = $this->getMockBuilder(Row::class)
      ->disableOriginalConstructor()
      ->getMock();

    $result = $plugin->transform([
      $field_type,
      $field_settings,
      $source_field_type,
    ], $executable, $row, 'foo');
    $this->assertSame($allowed_values, $result['allowed_values']);
  }

  /**
   * Provides field settings for testGetSettings().
   */
  public function getSettingsProvider() {
    return [
      [
        'list_integer',
        ['allowed_values' => "1|One\n2|Two\n3"],
        'list_integer',
        [
          '1' => 'One',
          '2' => 'Two',
          '3' => '3',
        ],
      ],
      [
        'list_string',
        ['allowed_values' => NULL],
        'list_integer',
        [],
      ],
      [
        'list_float',
        ['allowed_values' => ""],
        'list_integer',
        [],
      ],
      [
        'boolean',
        [],
        'boolean',
        [],
      ],
    ];
  }

}
