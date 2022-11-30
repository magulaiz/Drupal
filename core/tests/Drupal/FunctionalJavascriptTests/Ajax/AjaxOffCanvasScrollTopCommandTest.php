<?php

namespace Drupal\FunctionalJavascriptTests\Ajax;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Performs tests on AJAX framework commands.
 *
 * @group Ajax
 */
class AjaxOffCanvasScrollTopCommandTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ajax_test', 'ajax_forms_test', 'views'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The current session.
   */
  protected $session;

  /**
   * The current session.
   */
  protected $assert;

  /**
   * Amount of pixels that scrollTop adds to the scroll element.
   */
  protected $viewsScrollTopOffset;

  /**
   * Amount of pixels that scrollTop uses for animating scrollTop function.
   */
  protected $viewsScrollTopAnimateTime;

  /**
   * Amount of pixels for scroll offset.
   */
  protected $scrollOffset;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->session = $this->getSession();
    $this->assert = $this->assertSession();

    // Making window smaller to make scroll active.
    $this->session->resizeWindow(800, 250);

    // This offset comes from the js function
    // Drupal.AjaxCommands.prototype.viewsScrollTop().
    $this->viewsScrollTopOffset = 10;

    // This animate time comes from the js function
    // Drupal.AjaxCommands.prototype.viewsScrollTop().
    $this->viewsScrollTopAnimateTime = 500;

    // The amount of pixels to scroll for moving target out of the visible area.
    $this->scrollOffset = 600;
  }

  /**
   * Tests the scrollTop command on Page.
   */
  public function testAjaxScrollTopCommand() {
    $this->drupalGet('/ajax-test/scroll-top-test-action-page');
    $this->executeScrollTopAndTest();

  }

  /**
   * Tests the scrollTop command inside Off Canvas.
   */
  public function testAjaxScrollTopInsideOffCanvasCommand() {
    $this->drupalGet('/ajax-test/scroll-top-test-page');
    $this->clickLink('Open ScrollTop form in Off Canvas');
    $this->assert->assertWaitOnAjaxRequest();
    $this->assert->waitForElementVisible('css', 'div.ui-dialog-off-canvas');
    $this->executeScrollTopAndTest('#drupal-off-canvas');
  }

  /**
   * Tests the scrollTop command inside Dialog.
   */
  public function testAjaxScrollTopInsideDialogCommand() {
    $this->drupalGet('/ajax-test/scroll-top-test-page');
    $this->clickLink('Open ScrollTop form in Dialog');
    $this->assert->assertWaitOnAjaxRequest();
    $this->assert->waitForElementVisible('css', 'div.ui-dialog-content');
    $this->executeScrollTopAndTest('.ui-dialog-content');
  }

  /**
   * Helper function to test the scrollTop scrolling result position.
   */
  private function executeScrollTopAndTest($wrapper_selector = NULL) {
    if ($wrapper_selector) {
      $wrapper_selector = '"' . addslashes($wrapper_selector) . '"';
    }
    else {
      $wrapper_selector = 'document';
    }
    // Making scrolls on the parent elements.
    $this->session->executeScript("window.jQuery(document).scrollTop($this->scrollOffset);");
    $this->session->executeScript("window.jQuery($wrapper_selector).scrollTop($this->scrollOffset);");
    $this->clickLink('Execute scrollTop');
    $this->assert->assertWaitOnAjaxRequest();

    // Waiting for animate() to finish with additional 50 milliseconds overtime.
    $this->session->wait($this->viewsScrollTopAnimateTime + 50);

    $document_scroll = $this->session->evaluateScript("window.jQuery($wrapper_selector).scrollTop();");
    $off_canvas_content_scroll = $this->session
      ->evaluateScript("window.jQuery($wrapper_selector).scrollTop();");
    if ($wrapper_selector == 'document') {
      $off_canvas_content_top_relative = 0;
    }
    else {
      $off_canvas_content_top = $this->session->evaluateScript("window.jQuery($wrapper_selector).offset().top;");
      $off_canvas_content_top_relative = $off_canvas_content_top - $off_canvas_content_scroll;
    }
    $scrolling_element_top = $this->session->evaluateScript("window.jQuery(\"#scroll-top-scroll-target\").offset().top;");
    $scrolling_element_top_relative = $scrolling_element_top - $document_scroll;

    $this->assertEquals(
      floor($scrolling_element_top_relative - $off_canvas_content_top_relative),
      $this->viewsScrollTopOffset,
      'Scrolled position does not match the target element position.'
    );
  }

}
