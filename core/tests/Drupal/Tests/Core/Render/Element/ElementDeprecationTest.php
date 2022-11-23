<?php

namespace Drupal\Tests\Core\Render\Element;

use Drupal\Core\Render\Element;
use Drupal\Tests\UnitTestCase;

/**
 * Element Deprecation Test class.
 *
 * @group Render
 * @group legacy
 */
class ElementDeprecationTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    parent::setUp();
    include_once $this->root . '/core/includes/common.inc';
  }

  /**
   * Tests deprecation of show() function.
   */
  public function testShowDeprecation() {
    $this->expectDeprecation('show() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Render\Element::show() instead. See https://www.drupal.org/node/3261271');
    $element = [];
    $this->assertEquals(show($element), Element::show($element));
  }

  /**
   * Tests deprecation of hide() function.
   */
  public function testHideDeprecation() {
    $this->expectDeprecation('hide() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Core\Render\Element::hide() instead. See https://www.drupal.org/node/3261271');
    $element = [];
    $this->assertEquals(hide($element), Element::hide($element));
  }

}
