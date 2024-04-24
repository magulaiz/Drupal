<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Update;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * tests that update.php is accessible even if there are unstable modules.
 */
class UpdateReducedModuleListTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['update_test_broken_theme_hook'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * tests that the update page can be accessed.
   */
  public function testUpdatePage() {
    require_once $this->root . '/core/includes/update.inc';
    $this->writeSettings([
      'settings' => [
        'update_free_access' => (object) [
          'value' => TRUE,
          'required' => TRUE,
        ],
      ],
    ]);
    $this->drupalGet(Url::fromRoute('system.db_update'));
    $this->assertSession()->statusCodeEquals(200);
  }

}
