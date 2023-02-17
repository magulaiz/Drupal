<?php

namespace Drupal\system\Tests\Update;

/**
 * Tests system_post_update_resave_configuration().
 *
 * @group Update
 */
class ResaveConfigurationTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../tests/fixtures/update/drupal-8.3.x.bare-standard-plus-config_test.php.gz',
      __DIR__ . '/../../../tests/fixtures/update/drupal-8.config-resave.php',
    ];
  }

  /**
   * Ensures that the configuration is resaved so they have the correct sorting.
   */
  public function testUpdate() {
    $config = \Drupal::config('config_test.dynamic.update_test');
    $this->assertIdentical(['b', 'a'], array_keys($config->get('third_party_settings')));

    // Run the updates.
    $this->runUpdates();

    $config = \Drupal::config('config_test.dynamic.update_test');
    $this->assertIdentical(['a', 'b'], array_keys($config->get('third_party_settings')));
  }

}
