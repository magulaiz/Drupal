<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\System;

use Drupal\Tests\BrowserTestBase;

/**
 * Confirm that the Base URL value can be set and retrieved.
 *
 * @group system
 */
class BaseUrlTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with access to the Basic site settings form permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $permissions = [
      'administer site configuration',
    ];
    $this->adminUser = $this->drupalCreateUser($permissions);
  }

  /**
   * Test that the Base URL value can be saved and retrieved.
   */
  public function testBaseUrl(): void {
    $assert = $this->assertSession();

    // Log in as an admin user to allow access to admin pages.
    $this->drupalLogin($this->adminUser);

    // Test the Base URL field on the Basic site settings page.
    $this->drupalGet('admin/config/system/site-information');
    $page = $this->getSession()->getPage();

    // Site frontpage is required, just use a page which exists.
    $page->fillField('site_frontpage', '/admin/config/system/site-information');

    // Set a Base URL and check if it was saved.
    $page->fillField('base_url', 'https://mybaseurl.com');
    $this->submitForm([], 'Save configuration');
    $assert->pageTextContains('The configuration options have been saved.');

    // Verify that the Base URL setting has been saved and is available.
    $config = $this->config('system.site');
    $this->assertSame('https://mybaseurl.com', $config->get('page.base_url'));
  }

}
