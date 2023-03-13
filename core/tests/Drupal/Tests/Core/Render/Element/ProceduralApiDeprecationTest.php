<?php

namespace Drupal\Tests\Core\Render\Element;

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
  public function testShowHideDeprecation(): void {
    $element = [];
    $this->expectDeprecation('The global hide() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use to inline #printed=TRUE instead. See https://www.drupal.org/node/3261271');
    $this->assertEquals(['#printed' => TRUE], hide($element));
    $this->expectDeprecation('The global show() function is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use to inline #printed=FALSE instead. See https://www.drupal.org/node/3261271');
    $this->assertEquals(['#printed' => FALSE], show($element));
  }

}
