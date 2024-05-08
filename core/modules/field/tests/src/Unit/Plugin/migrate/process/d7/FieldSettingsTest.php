<?php

declare(strict_types=1);

namespace Drupal\Tests\field\Unit\Plugin\migrate\process\d7;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\field\Plugin\migrate\process\d7\FieldSettings;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Tests\migrate\Unit\MigrateTestCase;

/**
 * @group field
 */
#[CoversClass(\Drupal\field\Plugin\migrate\process\d7\FieldSettings::class)]
class FieldSettingsTest extends MigrateTestCase {

  /**
   * Tests transformation of image field settings.
   */
  public function testTransformImageSettings() {
    $migration = $this->createMock(MigrationInterface::class);
    $plugin = new FieldSettings([], 'd7_field_settings', []);

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
