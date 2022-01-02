<?php

namespace Drupal\Tests\system\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests that the drupalAutoButtons dialog option works correctly.
 *
 * @group system
 */
class DialogAutoButtonsTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'dialog_auto_buttons_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that dialogs respect the 'drupalAutoButtons' dialog option.
   */
  public function testModalRenderer() {
    $session_assert = $this->assertSession();

    // By default, buttons within "action" form elements are changed to jQuery
    // ui buttons and moved into the 'ui-dialog-buttonpane' container.
    $this->drupalGet('/dialog_auto_buttons-test-links');
    $this->clickLink('Default!');
    $session_assert->assertWaitOnAjaxRequest();
    $session_assert->elementExists('css', '.ui-dialog-buttonpane .ui-dialog-buttonset .js-form-submit');

    // When the drupalAutoButtons option is false, buttons SHOULD NOT be moved
    // into the 'ui-dialog-buttonpane' container.
    $this->drupalGet('/dialog_auto_buttons-test-links');
    $this->clickLink('Set to false!');
    $session_assert->assertWaitOnAjaxRequest();
    $session_assert->elementExists('css', '.form-actions');
    $session_assert->elementNotExists('css', '.ui-dialog-buttonpane');

    // When the drupalAutoButtons option is true, buttons SHOULD be moved
    // into the 'ui-dialog-buttonpane' container.
    $this->drupalGet('/dialog_auto_buttons-test-links');
    $this->clickLink('Set to true!');
    $session_assert->assertWaitOnAjaxRequest();
    $session_assert->elementExists('css', '.ui-dialog-buttonpane .ui-dialog-buttonset .js-form-submit');
  }

}
