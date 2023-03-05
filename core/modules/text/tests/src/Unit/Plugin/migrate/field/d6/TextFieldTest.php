<?php

namespace Drupal\Tests\text\Unit\Plugin\migrate\field\d6;

use Drupal\Tests\UnitTestCase;
use Drupal\text\Plugin\migrate\field\d6\TextField;

/**
 * @coversDefaultClass \Drupal\text\Plugin\migrate\field\d6\TextField
 * @group text
 * @group legacy
 */
class TextFieldTest extends UnitTestCase {

  /**
   * Tests deprecation of TextField plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\text\Plugin\migrate\field\d6\TextField is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d6\TextField instead. See https://www.drupal.org/node/1234567');
    new TextField([], 'text', []);
  }

}
