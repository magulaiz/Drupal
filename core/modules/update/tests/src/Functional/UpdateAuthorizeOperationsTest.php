<?php

declare(strict_types=1);

namespace Drupal\Tests\update\Functional;

use Drupal\Core\Site\Settings;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests allow_authorize_operations in settings.php.
 *
 * @group update
 */
class UpdateAuthorizeOperationsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Paths affected by the allow_authorize_operations setting.
   *
   * @var string[]
   */
  protected static $protectedPaths = [
    '/admin/reports/updates/install',
    '/admin/reports/updates/update',
    '/admin/modules/install',
    '/admin/modules/update',
    '/admin/theme/install',
    '/admin/appearance/update',
    '/admin/update/ready',
  ];

  /**
   * Test that access to protected routes is controlled by the setting.
   */
  public function testProtectedRoutes(): void {
    $account = $this->createUser(['administer software updates']);
    $admin_account = $this->createUser([], NULL, TRUE);

    // By default, the test user can access all the protected paths.
    $this->drupalLogin($account);
    foreach (static::$protectedPaths as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(200);
    }

    // If the setting is false, not even an admin user can access these paths.
    $settings = Settings::getAll();
    $settings['allow_authorize_operations'] = FALSE;
    new Settings($settings);
    $this->drupalLogin($admin_account);
    foreach (static::$protectedPaths as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(403);
    }

    // If the setting is true, the test user can access all the protected paths.
    $settings = Settings::getAll();
    $settings['allow_authorize_operations'] = TRUE;
    new Settings($settings);
    $this->drupalLogin($account);
    foreach (static::$protectedPaths as $path) {
      $this->drupalGet($path);
      $this->assertSession()->statusCodeEquals(200);
    }
  }

}
