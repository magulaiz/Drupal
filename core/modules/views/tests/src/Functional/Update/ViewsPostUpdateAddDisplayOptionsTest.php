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
 * @group update
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
      __DIR__ . '/../../../fixtures/update/views-add-display-options-update.php',
    ];
  }

  /**
   * Tests the upgrade path for adding 'style_options' and 'pager_options'.
   */
  public function testViewsAddDisplayOptionsUpdate() {
    // Load the view to test the update on.
    $view = View::load('test_view');
    $displayOptions = $view->get('display')['default']['display_options'];

    // Assert 'style_options' and 'pager_options' are missing before the update.
    $this->assertFalse(isset($displayOptions['style_options']), "Before update: 'style_options' is missing.");
    $this->assertFalse(isset($displayOptions['pager_options']), "Before update: 'pager_options' is missing.");

    // Run the update process.
    $this->runUpdates();

    // Reload the view after the update.
    $view = View::load('test_view');
    $displayOptions = $view->get('display')['default']['display_options'];

    // Assert 'style_options' and 'pager_options' have been added by the update.
    $this->assertTrue(isset($displayOptions['style_options']), "After update: 'style_options' has been added.");
    $this->assertTrue(isset($displayOptions['pager_options']), "After update: 'pager_options' has been added.");
  }

}
