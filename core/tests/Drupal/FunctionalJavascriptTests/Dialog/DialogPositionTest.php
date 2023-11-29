<?php

declare(strict_types=1);

namespace Drupal\FunctionalJavascriptTests\Dialog;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the JavaScript functionality of the dialog position.
 *
 * @group dialog
 */
class DialogPositionTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'entity_test',
    'node',
    'field',
    'field_ui',
    'off_canvas_test',
    'dialog_renderer_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser([
      'administer blocks',
      'administer entity_test fields',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests if the dialog UI works properly with block layout page.
   */
  public function testDialogOpenAndClose() {
    $this->drupalGet('admin/structure/block');
    $session = $this->getSession();
    $assert_session = $this->assertSession();
    $page = $session->getPage();

    // Open the dialog using the place block link.
    $placeBlockLink = $page->findLink('Place block');
    $this->assertTrue($placeBlockLink->isVisible(), 'Place block button exists.');
    $placeBlockLink->click();
    $assert_session->assertWaitOnAjaxRequest();
    $dialog = $page->find('css', '.ui-dialog');
    $this->assertTrue($dialog->isVisible(), 'Dialog is opened after clicking the Place block button.');

    // Close the dialog again.
    $closeButton = $page->find('css', '.ui-dialog-titlebar-close');
    $closeButton->click();
    $dialog = $page->find('css', '.ui-dialog');
    $this->assertNull($dialog, 'Dialog is closed after clicking the close button.');

    // Resize the window. The test should pass after waiting for JavaScript to
    // finish as no Javascript errors should have been triggered. If there were
    // javascript errors the test will fail on that.
    $session->resizeWindow(625, 625);
    usleep(5000);
  }

  /**
   * Tests dialog resizing on window resize.
   */
  public function testModalWidthResizing() {
    $this->drupalGet('entity_test/structure/entity_test/fields');
    $page = $this->getSession()->getPage();

    $page->pressButton('List additional actions');
    $page->findLink('Delete')->click();
    $this->assertSession()->waitForElementVisible('css', '[role="dialog"]');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $script = <<<SCRIPT
      (function() {
        return document.querySelector('.modal-dialog').style.width;
      }())
      SCRIPT;

    // Resize the window.
    $width_before = $this->getSession()->getDriver()->evaluateScript($script);
    $this->getSession()->resizeWindow(785, 805);
    $width_after = $this->getSession()->getDriver()->evaluateScript($script);
    $this->assertEquals($width_before, $width_after);
  }

}
