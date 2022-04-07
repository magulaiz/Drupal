<?php

namespace Drupal\Tests\system\Functional\System;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\system\Functional\Cache\AssertPageCacheContextsAndTagsTrait;

/**
 * Tests page access denied functionality as authenticated user.
 *
 * @group system
 */
class AccessDeniedAsAuthenticatedUserTest extends BrowserTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'node', 'system_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The administrator user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * The user account without access.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $deniedUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');

    // Create an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer site configuration',
      'link to any page',
      'administer blocks',
    ]);
    $this->adminUser->roles[] = 'administrator';
    $this->adminUser->save();

    // Create a user without any permissions.
    $this->deniedUser = $this->drupalCreateUser(['access user profiles']);
  }

  /**
   * Tests access denied.
   */
  public function testAccessDenied() {
    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('admin');
    $this->assertSession()->pageTextContains('Access denied');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();

    $this->drupalLogin($this->adminUser);

    // Set a custom 403 page to a page that redirects.
    $edit = [
      'site_403' => '/user',
    ];
    $this->drupalGet('admin/config/system/site-information');
    $this->submitForm($edit, 'Save configuration');

    $this->drupalLogout();
    $this->drupalLogin($this->deniedUser);
    $this->drupalGet('admin');
    $this->assertSession()->pageTextContains($this->deniedUser->getAccountName());
    $this->assertSession()->pageTextContains('Username');
  }

}
