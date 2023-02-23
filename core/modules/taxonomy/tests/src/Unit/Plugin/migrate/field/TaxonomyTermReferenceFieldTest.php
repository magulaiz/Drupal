<?php

namespace Drupal\Tests\taxonomy\Unit\Plugin\migrate\field;

use Drupal\Tests\UnitTestCase;
use Drupal\taxonomy\Plugin\migrate\field\TaxonomyTermReference;

/**
 * @coversDefaultClass \Drupal\taxonomy\Plugin\migrate\field\TaxonomyTermReference
 * @group taxonomy
 * @group legacy
 */
class TaxonomyTermReferenceFieldTest extends UnitTestCase {

  /**
   * Tests deprecation of TaxonomyTermReference plugin.
   */
  public function testDeprecatedPlugin() {
    $this->expectDeprecation('Drupal\taxonomy\Plugin\migrate\field\TaxonomyTermReference is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\TaxonomyTermReference instead. See https://www.drupal.org/node/1234567');
    new TaxonomyTermReference([], 'taxonomy', []);
  }

}
