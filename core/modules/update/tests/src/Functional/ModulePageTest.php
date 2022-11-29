<?php

namespace Drupal\Tests\update\Functional;

use Drupal\Core\Url;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Tests functionality of the Update module specific to the module page.
 *
 * @group update
 */
class ModulePageTest extends UpdateTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['language'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $setting = [
      '#all' => [
        'version' => '8.0.0',
      ],
    ];
    $this->config('update_test.settings')->set('system_info', $setting)->save();
  }

  /**
   * Checks the messages at admin/modules when the site is up-to-date.
   */
  public function testModulePageUpToDate(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'administer site configuration',
    ]));
    // Instead of using refreshUpdateStatus(), set these manually.
    $this->config('update.settings')
      ->set('fetch.url', Url::fromRoute('update_test.update_test')
        ->setAbsolute()
        ->toString())
      ->save();
    $this->config('update_test.settings')
      ->set('xml_map', ['drupal' => '0.0'])
      ->save();

    $this->drupalGet('admin/reports/updates');
    $this->clickLink('Check manually');
    $this->checkForMetaRefresh();
    $this->assertSession()->pageTextContains('Checked available update data for one project.');
    $this->drupalGet('admin/modules');
    $this->assertSession()->pageTextNotContains('There are updates available for your version of Drupal.');
    $this->assertSession()->pageTextNotContains('There is a security update available for your version of Drupal.');
  }

  /**
   * Checks the messages at admin/modules when an update is missing.
   */
  public function testModulePageRegularUpdate(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'administer site configuration',
      'administer modules',
    ]));
    // Instead of using refreshUpdateStatus(), set these manually.
    $this->config('update.settings')
      ->set('fetch.url', Url::fromRoute('update_test.update_test')
        ->setAbsolute()
        ->toString())
      ->save();
    $this->config('update_test.settings')
      ->set('xml_map', ['drupal' => '0.1'])
      ->save();

    $this->drupalGet('admin/reports/updates');
    $this->clickLink('Check manually');
    $this->checkForMetaRefresh();
    $this->assertSession()->pageTextContains('Checked available update data for one project.');
    $this->drupalGet('admin/modules');
    $this->assertSession()->pageTextContains('There are updates available for your version of Drupal.');
    $this->assertSession()->pageTextNotContains('There is a security update available for your version of Drupal.');
  }

  /**
   * Checks the messages at admin/modules when a security update is missing.
   */
  public function testModulePageSecurityUpdate(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'administer site configuration',
      'administer modules',
      'administer themes',
    ]));
    // Instead of using refreshUpdateStatus(), set these manually.
    $this->config('update.settings')
      ->set('fetch.url', Url::fromRoute('update_test.update_test')
        ->setAbsolute()
        ->toString())
      ->save();
    $this->config('update_test.settings')
      ->set('xml_map', ['drupal' => 'sec.0.2'])
      ->save();

    $this->drupalGet('admin/reports/updates');
    $this->clickLink('Check manually');
    $this->checkForMetaRefresh();
    $this->assertSession()->pageTextContains('Checked available update data for one project.');
    $this->drupalGet('admin/modules');
    $this->assertSession()->pageTextNotContains('There are updates available for your version of Drupal.');
    $this->assertSession()->pageTextContains('There is a security update available for your version of Drupal.');

    // Make sure admin/appearance warns you're missing a security update.
    $this->drupalGet('admin/appearance');
    $this->assertSession()->pageTextNotContains('There are updates available for your version of Drupal.');
    $this->assertSession()->pageTextContains('There is a security update available for your version of Drupal.');

    // Make sure duplicate messages don't appear on Update status pages.
    $this->drupalGet('admin/reports/status');
    $this->assertSession()->pageTextContainsOnce('There is a security update available for your version of Drupal.');

    $this->drupalGet('admin/reports/updates');
    $this->assertSession()->pageTextNotContains('There is a security update available for your version of Drupal.');

    $this->drupalGet('admin/reports/updates/settings');
    $this->assertSession()->pageTextNotContains('There is a security update available for your version of Drupal.');
  }

  /**
   * Checks that running cron updates the list of available updates.
   */
  public function testModulePageRunCron(): void {
    $this->config('update.settings')
      ->set('fetch.url', Url::fromRoute('update_test.update_test')
        ->setAbsolute()
        ->toString())
      ->save();
    $this->config('update_test.settings')
      ->set('xml_map', ['drupal' => '0.0'])
      ->save();

    $this->cronRun();
    $this->drupalGet('admin/modules');
    $this->assertSession()->pageTextNotContains('No update information available.');
  }

  /**
   * Checks language module in core package at admin/reports/updates.
   */
  public function testLanguageModuleUpdate(): void {
    $this->drupalLogin($this->drupalCreateUser([
      'administer site configuration',
    ]));
    // Instead of using refreshUpdateStatus(), set these manually.
    $this->config('update.settings')
      ->set('fetch.url', Url::fromRoute('update_test.update_test')
        ->setAbsolute()
        ->toString())
      ->save();
    $this->config('update_test.settings')
      ->set('xml_map', ['drupal' => '0.1'])
      ->save();

    $this->drupalGet('admin/reports/updates');
    $this->assertSession()->pageTextContains('Language');
  }

}
