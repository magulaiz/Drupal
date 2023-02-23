<?php

namespace Drupal\Tests\link\Unit\Plugin\migrate\field\d6;

use Drupal\Tests\UnitTestCase;
use Drupal\link\Plugin\migrate\field\d6\LinkField;

/**
 * @coversDefaultClass \Drupal\link\Plugin\migrate\field\d6\LinkField
 * @group link
 * @group legacy
 */
class LinkFieldTest extends UnitTestCase {

  /**
   * Tests deprecation of LinkField plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\link\Plugin\migrate\field\d6\LinkField is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d6\LinkField instead. See https://www.drupal.org/node/1234567');
    new LinkField([], 'text', []);
  }

}
