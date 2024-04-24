<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Update;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * tests that update.php is accessible even if there are unstable modules.
 */
class UpdateReducedThemeRegistryTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update_test_broken_theme_hook'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the update page can be accessed.
   */
  public function testUpdatePageWithFilterOn() {
    require_once $this->root . '/core/includes/update.inc';
    $this->writeSettings([
      'settings' => [
        'update_free_access' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
        'update_theme_registry_module_filter' => (object) [
          'value' => ['system'],
          'required' => TRUE,
        ],
      ],
    ]);
    $this->drupalGet(Url::fromRoute('system.db_update'));
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests that the update page breaks with unstable data.
   */
  public function testUpdatePageWithFilterOff() {
    require_once $this->root . '/core/includes/update.inc';
    $this->writeSettings([
      'settings' => [
        'update_free_access' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
        'update_theme_registry_module_filter' => (object) [
          'value' => FALSE,
          'required' => TRUE,
        ],
      ],
    ]);
    $this->expectExceptionMessage('Exception: This mimics an exception caused by unstable dependencies.');
    $this->drupalGet(Url::fromRoute('system.db_update'));
  }

}
