<?php

namespace Drupal\Tests\system\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests creation of system linkset settings.
 *
 * @see system_post_update_linkset_settings()
 *
 * @group Update
 */
class MenuLinksetSettingsUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../fixtures/update/drupal-9.3.0.bare.standard.php.gz',
    ];
  }

  /**
   * Tests system_post_update_linkset_settings().
   */
  public function testSystemPostUpdateLinksetSettings() {
    $this->runUpdates();

    // Confirm that config was created and the endpoint is disabled.
    $config = $this->config('system.linkset');
    $this->assertTrue($config->isNew());
    $this->assertFalse($config->get('enable_endpoint'));
  }

}
