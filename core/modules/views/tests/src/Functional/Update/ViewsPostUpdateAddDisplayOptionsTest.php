<?php

namespace Drupal\Tests\views\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\views\Entity\View;

/**
 * Tests the post-update addition of 'style_options' and 'pager_options'.
 *
 * @see views_post_update_add_display_options()
 *
 * @group views
 * @group Update
 */
class ViewsPostUpdateAddDisplayOptionsTest extends UpdatePathTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views'];

  /**
   * Sets the path to database dumps.
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/add_style_and_pager_options_update.php',
    ];
  }

  /**
   * Tests the upgrade path for adding 'style_options' and 'pager_options'.
   */
  public function testViewsAddDisplayOptionsUpdateForAllDisplays() {
    // Load the view to test the update on.
    $view = View::load('add_display_options_update');

    // Assert specific configurations are missing before the update for each display.
    foreach ($view->get('display') as $displayId => $display) {
      $this->assertArrayNotHasKey('style_options', $display['display_options'], "Before update: 'style' type is missing in display '{$displayId}'.");
      $this->assertArrayNotHasKey('pager_options', $display['display_options'], "Before update: 'pager' options are missing in display '{$displayId}'.");
    }

    // Run the update process.
    $this->runUpdates();

    // Reload the view after the update.
    $view = View::load('add_display_options_update');

    // Assert specific style and pager configurations have been added or
    // modified by the update for each display.
    foreach ($view->get('display') as $displayId => $display) {
      $this->assertArrayHasKey('style_options', $display['display_options'], "After update: 'style' type has been added or modified in display '{$displayId}'.");
      $this->assertArrayHasKey('pager_options', $display['display_options'], "After update: 'pager' options have been added or modified in display '{$displayId}'.");
    }
  }

}
