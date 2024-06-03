<?php

declare(strict_types=1);

namespace Drupal\FunctionalJavascriptTests;

/**
 * Tests that sticky header can be toggled.
 *
 * @group javascript
 */
class StickyHeaderToggleTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Tests the checkbox for enabling/disabling table sticky header.
   */
  public function testStickyDisabled(): void {
    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/admin/content');
    $assert_session = $this->assertSession();
    $checkbox = $assert_session->elementExists('css', '.tableheader-toggle-sticky input[type="checkbox"]');

    // Confirm that the table header is sticky if the checkbox is checked.
    $this->assertTrue($checkbox->isChecked());
    $this->getSession()->evaluateScript('scroll(0, document.documentElement.scrollTop + 1500);');
    $assert_session->assertVisibleInViewport('css', '.views-table thead');

    // Confirm that the table header is not sticky if the checkbox is unchecked.
    $this->getSession()->evaluateScript('scroll(0, document.documentElement.scrollTop - 1500);');
    $checkbox->uncheck();
    $this->assertFalse($checkbox->isChecked());
    $assert_session->assertNotVisibleInViewport('css', '.views-table thead');
  }

}
