<?php

namespace Drupal\Tests\file\Unit\Plugin\migrate\field\d7;

use Drupal\Tests\UnitTestCase;
use Drupal\file\Plugin\migrate\field\d7\FileField;

/**
 * @coversDefaultClass \Drupal\file\Plugin\migrate\field\d7\FileField
 * @group file
 * @group legacy
 */
class FileFieldTest extends UnitTestCase {

  /**
   * Tests deprecation of FileField plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\file\Plugin\migrate\field\d7\FileField is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d7\FileField instead. See https://www.drupal.org/node/1234567');
    new FileField([], 'file', []);
  }

}
