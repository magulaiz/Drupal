<?php

namespace Drupal\Tests\Core\Theme;

use Drupal\Core\Theme\ThemeHook;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Theme\ThemeHook
 * @group Theme
 */
class ThemeHookTest extends UnitTestCase {

  /**
   * @covers ::process
   */
  public function testProcessBadFunctionCall() {
    $hook = ThemeHook::create('foo')
      ->setRenderElement('elements')
      ->setFunction('nonexistent');
    $this->expectException(\BadFunctionCallException::class);
    $this->expectExceptionMessage('Theme hook "foo" refers to a theme function callback that does not exist: "nonexistent"');
    $hook->process('', '', []);
  }

}
