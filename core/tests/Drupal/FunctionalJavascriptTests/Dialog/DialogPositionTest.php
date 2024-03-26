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
    'field_ui',
    'ajax_test',
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
        return document.querySelector('.ui-dialog').clientWidth;
      }())
      SCRIPT;
    $width_before = $this->getSession()->getDriver()->evaluateScript($script);
    // This is the original width of the modal.
    $this->assertEquals('886', $width_before);

    // Resize the window near to the breaking point.
    $this->getSession()->resizeWindow(870, 805);
    $width_after = $this->getSession()->getDriver()->evaluateScript($script);
    $this->assertEquals('876', $width_after);

    // Resize the window.
    $this->getSession()->resizeWindow(1300, 1300);
    $width_after_resize = $this->getSession()->getDriver()->evaluateScript($script);
    // Assert that the width is restored to full size.
    $this->assertEquals('886', $width_after_resize);
    $this->drupalGet('ajax-test/dialog');
    $this->clickLink('Link 3 (non-modal)');
    $this->assertSession()->waitForElementVisible('css', '[role="dialog"]');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $width_before = $this->getSession()->getDriver()->evaluateScript($script);
    $this->assertEquals('806', $width_before);

    // Resize the window near to the breaking point.
    $this->getSession()->resizeWindow(780, 805);
    $width_after = $this->getSession()->getDriver()->evaluateScript($script);
    $this->assertEquals('786', $width_after);

    // Resize the window to bigger size to get the full modal width.
    $this->getSession()->resizeWindow(1300, 1300);
    $width_after_resize = $this->getSession()->getDriver()->evaluateScript($script);
    // Assert that the width is restored to full size.
    $this->assertEquals('806', $width_after_resize);
  }

}
