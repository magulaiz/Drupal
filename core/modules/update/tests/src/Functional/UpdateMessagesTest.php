<?php

namespace Drupal\Tests\update\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * @covers \update_page_top()
 *
 * @group update
 */
class UpdateMessagesTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update', 'update_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Routes that should not show any update notices or warnings.
   *
   * @var string[]
   */
  private $ignoredRoutes = [
    'update.theme_update',
    'update.module_update',
    'update.module_install',
    'update.status',
    'update.report_update',
    'update.report_install',
    'update.settings',
    'update.confirmation_page',
  ];

  /**
   * Routes that should always show update notices or warnings.
   *
   * @var string[]
   */
  private $verboseRoutes = [
    'system.modules_list',
    'system.themes_page',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Use our XML fixtures to get info about available updates.
    $this->config('update.settings')
      ->set('fetch.url', "$this->baseUrl/update-test")
      ->save();
  }

  /**
   * Tests the display of warnings for available security updates.
   */
  public function testSecurityWarnings(): void {
    // Pretend we're on Drupal 8.0.0, and that a security update is available.
    $this->config('update_test.settings')
      ->set('xml_map', [
        'drupal' => 'sec.0.2',
      ])
      ->set('system_info', [
        '#all' => [
          'version' => '8.0.0',
        ],
      ])
      ->save();

    $this->drupalLogin($this->rootUser);

    $this->drupalGet('/admin/reports/updates');
    $this->clickLink('Check manually');
    $this->checkForMetaRefresh();
    $this->assertSecurityWarning();

    // None of the ignored routes should show the security warning.
    foreach ($this->ignoredRoutes as $route) {
      $url = Url::fromRoute($route);
      $this->drupalGet($url);
      $this->assertNoSecurityWarning();
    }
    // The system.theme_install route requires a CSRF token, so it must be
    // accessed interactively.
    $this->drupalGet('/admin/appearance');
    $this->clickLink('Install Claro theme');
    $this->assertNoSecurityWarning();

    // The status report should only display the security warning once (i.e.,
    // in the actual report, but not in the messages).
    $this->drupalGet('/admin/reports/status');
    $this->assertSecurityWarning();

    // The module and theme lists should show the security warning.
    foreach ($this->verboseRoutes as $route) {
      $url = Url::fromRoute($route);
      $this->drupalGet($url);
      $this->assertSecurityWarning();
    }

    // Any other admin page should show the security warning.
    $this->drupalGet('/admin/structure');
    $this->assertSecurityWarning();
    $this->drupalGet('/admin/config');
    $this->assertSecurityWarning();
  }

  /**
   * Tests the display of notices for available non-security updates.
   */
  public function testAvailableUpdate(): void {
    // Pretend we're on Drupal 8.0.0, and that a non-security update is
    // available.
    $this->config('update_test.settings')
      ->set('xml_map', [
        'drupal' => '1.0',
      ])
      ->set('system_info', [
        '#all' => [
          'version' => '8.0.0',
        ],
      ])
      ->save();

    $this->drupalLogin($this->rootUser);

    $this->drupalGet('/admin/reports/updates');
    $this->clickLink('Check manually');
    $this->checkForMetaRefresh();
    // We should not see a notice about the update, since it's not a security
    // update.
    $this->assertNoUpdateNotice();

    // None of the ignored routes should show the update notice.
    foreach ($this->ignoredRoutes as $route) {
      $url = Url::fromRoute($route);
      $this->drupalGet($url);
      $this->assertNoUpdateNotice();
    }

    $assert_session = $this->assertSession();

    // The "verbose" routes should always show the notice.
    foreach ($this->verboseRoutes as $route) {
      $url = Url::fromRoute($route);
      $this->drupalGet($url);
      $assert_session->pageTextContains('There are updates available for your version of Drupal.');
    }

    // Other admin pages shouldn't show the notice, since it's not a security
    // update warning.
    $this->drupalGet('/admin/structure');
    $this->assertNoUpdateNotice();
    $this->drupalGet('/admin/config');
    $this->assertNoUpdateNotice();
  }

  /**
   * Asserts that notices about available non-security updates aren't displayed.
   */
  private function assertNoUpdateNotice(): void {
    $assert_session = $this->assertSession();
    // To avoid false positives, ensure that the page loaded correctly.
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextNotContains('There are updates available for your version of Drupal.');
  }

  /**
   * Asserts that a warning about available security updates is NOT displayed.
   */
  private function assertNoSecurityWarning(): void {
    $assert_session = $this->assertSession();
    // To avoid false positives, ensure that the page loaded correctly.
    $assert_session->statusCodeEquals(200);
    $assert_session->pageTextNotContains('There is a security update available for your version of Drupal.');
  }

  /**
   * Asserts that a warning about available security updates is displayed.
   */
  private function assertSecurityWarning(): void {
    $this->assertSession()
      ->pageTextContainsOnce('There is a security update available for your version of Drupal.');
  }

}
