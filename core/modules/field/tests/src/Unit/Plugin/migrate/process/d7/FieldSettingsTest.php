<?php

namespace Drupal\Tests\field\Unit\Plugin\migrate\process\d7;

use Drupal\field\Plugin\migrate\process\d7\FieldSettings;
use Drupal\image\Plugin\migrate\field\d7\ImageField;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\MigrateFieldPluginManagerInterface;
use Drupal\Tests\migrate\Unit\MigrateTestCase;

/**
 * @coversDefaultClass \Drupal\field\Plugin\migrate\process\d7\FieldSettings
 * @group field
 */
class FieldSettingsTest extends MigrateTestCase {

  /**
   * Tests transformation of image field settings.
   *
   * @covers ::transform
   */
  public function testTransformImageSettings() {
    $migrate_field_plugin_manager = $this->createMock(MigrateFieldPluginManagerInterface::class);
    $migrate_field_plugin_manager->expects($this->once())->method('createInstance')->willReturn(new ImageField([], '', []));
    $plugin = new FieldSettings([], 'd7_field_settings', [], $migrate_field_plugin_manager);

    $executable = $this->createMock(MigrateExecutableInterface::class);
    $row = $this->getMockBuilder(Row::class)
      ->disableOriginalConstructor()
      ->getMock();

    $row->expects($this->atLeastOnce())
      ->method('getSourceProperty')
      ->willReturnMap([
        ['settings', ['default_image' => NULL]],
        ['type', 'image'],
      ]);

    $value = $plugin->transform([], $executable, $row, 'foo');
    $this->assertIsArray($value);
    $this->assertSame('', $value['default_image']['uuid']);
  }

}
