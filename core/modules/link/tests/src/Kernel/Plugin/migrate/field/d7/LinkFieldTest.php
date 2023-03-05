<?php

namespace Drupal\Tests\link\Kernel\Plugin\migrate\field\d7;

use Drupal\KernelTests\KernelTestBase;
use Drupal\link\Plugin\migrate\field\d7\LinkField;

/**
 * @coversDefaultClass \Drupal\link\Plugin\migrate\field\d7\LinkField
 * @group link
 * @group legacy
 */
class LinkFieldTest extends KernelTestBase {

  /**
   * Tests deprecation of LinkField plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\link\Plugin\migrate\field\d7\LinkField is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d7\LinkField instead. See https://www.drupal.org/node/1234567');
    new LinkField([], 'text', []);
  }

}
