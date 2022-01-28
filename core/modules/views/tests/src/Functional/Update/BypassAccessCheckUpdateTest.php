<?php

namespace Drupal\Tests\views\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the addition of 'bypass_access_check' option to link fields.
 *
 * @see views_post_update_bypass_access_check()
 *
 * @group views
 * @group legacy
 */
class BypassAccessCheckUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/bypass-access-check.php',
    ];
  }

  /**
   * Tests the addition of 'bypass_access_check' option to link fields.
   *
   * @see views_post_update_bypass_access_check()
   */
  public function testBypassAccessCheckUpdate() {
    $trail = 'display.default.display_options.fields.view_node';

    // Check that no 'bypass_access_check' option exists for entity_link field.
    $view = $this->config('views.view.test_link_bypass_access_check');
    $this->assertArrayNotHasKey('bypass_access_check', $view->get($trail));

    $this->runUpdates();

    // Check that the 'bypass_access_check' option has been added as FALSE.
    $view = $this->config('views.view.test_link_bypass_access_check');
    $this->assertArrayHasKey('bypass_access_check', $view->get($trail));
    $this->assertFalse($view->get("$trail.bypass_access_check"));
  }

}
