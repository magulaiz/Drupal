<?php

namespace Drupal\Tests\user\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests user_post_update_configure_cancel_options() upgrade path.
 *
 * @group Update
 * @group legacy
 */
class UserUpdateCancelOptionsTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles[] = __DIR__ . '/../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz';
  }

  /**
   * Tests that cancel methods are set correctly on user.settings.
   */
  public function testRolePermissions() {

    $cancel_methods = $this->config('user.settings')->get('cancel_methods');
    $this->assertNull($cancel_methods);

    // Run updates.
    $this->runUpdates();

    $config = $this->config('user.settings');
    $cancel_methods = $config->get('cancel_methods');
    $this->assertArrayHasKey('user_cancel_block', $cancel_methods);
    $this->assertEquals($cancel_methods['user_cancel_block'], TRUE);
    $this->assertArrayHasKey('user_cancel_block_unpublish', $cancel_methods);
    $this->assertEquals($cancel_methods['user_cancel_block_unpublish'], TRUE);
    $this->assertArrayHasKey('user_cancel_reassign', $cancel_methods);
    $this->assertEquals($cancel_methods['user_cancel_reassign'], TRUE);
    $this->assertArrayHasKey('user_cancel_delete', $cancel_methods);
    $this->assertEquals($cancel_methods['user_cancel_delete'], TRUE);

    // Check valid Schema after update.
    $this->assertConfigSchema(\Drupal::service('config.typed'), 'user.settings', $config->get());

  }

}
