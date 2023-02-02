<?php

namespace Drupal\Tests\Core\Render\Element;

use Drupal\Core\Render\Element;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the deprecation of global rendering functions.
 *
 * @group Render
 * @group legacy
 */
class ProceduralApiDeprecationTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    include_once $this->root . '/core/includes/common.inc';
  }

  /**
   * Tests the deprecation of the global show() function.
   */
  public function testShowDeprecation(): void {
    $this->expectDeprecation('The global show() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Render\Element::show() instead. See https://www.drupal.org/node/3261271');
    $element1 = [];
    $element2 = [];
    $this->assertEquals(show($element1), Element::show($element2));
  }

  /**
   * Tests the deprecation of the global hide() function.
   */
  public function testHideDeprecation(): void {
    $this->expectDeprecation('The global hide() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Render\Element::hide() instead. See https://www.drupal.org/node/3261271');
    $element1 = [];
    $element2 = [];
    $this->assertEquals(hide($element1), Element::hide($element2));
  }

}
