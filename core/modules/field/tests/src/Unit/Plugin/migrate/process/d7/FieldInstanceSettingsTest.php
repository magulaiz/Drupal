<?php

namespace Drupal\Tests\field\Unit\Plugin\migrate\process\d7;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\field\Plugin\migrate\process\d7\FieldInstanceSettings;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\MigrateFieldPluginManagerInterface;
use Drupal\Tests\migrate\Unit\MigrateTestCase;

/**
 * @coversDefaultClass \Drupal\field\Plugin\migrate\process\d7\FieldInstanceSettings
 * @group field
 */
class FieldInstanceSettingsTest extends MigrateTestCase {

  /**
   * Tests transformation of image field settings.
   *
   * @covers ::transform
   */
  public function testTransformImageSettings() {
    $migrate_field_plugin_manager = $this->createMock(MigrateFieldPluginManagerInterface::class);
    $migrate_field_plugin_manager->expects($this->once())->method('createInstance')->willThrowException(new PluginNotFoundException('image_image'));
    $plugin = new FieldInstanceSettings([], 'd7_field_instance_settings', [], $migrate_field_plugin_manager);

    $executable = $this->createMock(MigrateExecutableInterface::class);
    $row = $this->getMockBuilder(Row::class)
      ->disableOriginalConstructor()
      ->getMock();

    $value = $plugin->transform([[], ['type' => 'image_image'], ['data' => '']], $executable, $row, 'foo');
    $this->assertIsArray($value['default_image']);
    $this->assertSame('', $value['default_image']['alt']);
    $this->assertSame('', $value['default_image']['title']);
    $this->assertNull($value['default_image']['width']);
    $this->assertNull($value['default_image']['height']);
    $this->assertSame('', $value['default_image']['uuid']);
  }

}
