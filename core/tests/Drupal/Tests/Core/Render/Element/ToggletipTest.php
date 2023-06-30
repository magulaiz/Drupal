<?php

namespace Drupal\FunctionalJavascriptTests\Core\Render\Element;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

// cSpell:ignore toggletip

/**
 * Tests for the toggletip element.
 *
 * @group Render
 */
class ToggletipTest extends WebDriverTestBase
{

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['toggletip_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void
  {
    parent::setUp();
    $user = $this->createUser(['access content']);
    $this->drupalLogin($user);
    $this->drupalGet('/toggletip-test/toggletips');
  }

  /**
   * General splitbutton tests.
   *
   * @dataProvider providerTestSplitbuttons
   */
  public function testSplitbuttons($theme_name)
  {


  }
}
