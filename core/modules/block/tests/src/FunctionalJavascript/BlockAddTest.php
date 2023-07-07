<?php

namespace Drupal\Tests\block\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the JS functionality in the block add form.
 *
 * @group block
 */
class BlockAddTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the AJAX for the theme selector.
   */
  public function testBlockAddThemeSelector() {
    \Drupal::service('theme_installer')->install(['claro']);

    $this->drupalLogin($this->drupalCreateUser([
      'administer blocks',
    ]));

    $this->drupalGet('admin/structure/block/add/system_powered_by_block');
    $assert_session = $this->assertSession();

    // Using selector because the label collides with current theme condition.
    $theme_select = $this->cssSelect('.block-form > .form-item-theme select');
    $this->assertCount(1, $theme_select);
    $theme_select[0]->selectOption('claro');

    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->selectExists('Region')->selectOption('pre_content');
    $assert_session->assertWaitOnAjaxRequest();
    // Switch to a theme that doesn't contain the region selected above.
    $theme_select[0]->selectOption('stark');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->pageTextNotContains('The submitted value Pre-content in the Region element is not allowed.');
    $assert_session->optionExists('Region', '- Select -');
  }

}
