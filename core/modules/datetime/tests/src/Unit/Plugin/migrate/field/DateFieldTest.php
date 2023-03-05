<?php

namespace Drupal\Tests\datetime\Unit\Plugin\migrate\field;

use Drupal\datetime\Plugin\migrate\field\DateField;
use Drupal\Tests\UnitTestCase;

/**
 * Provides unit tests for the DateField Plugin.
 *
 * @coversDefaultClass \Drupal\datetime\Plugin\migrate\field\DateField
 *
 * @group migrate
 * @group legacy
 */
class DateFieldTest extends UnitTestCase {

  /**
   * Tests deprecation of DateField plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\datetime\Plugin\migrate\field\DateField is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\DateField instead. See https://www.drupal.org/node/1234567');
    new DateField([], '', []);
  }

}
