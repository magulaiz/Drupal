<?php

namespace Drupal\Tests\views\Kernel;

use Drupal\Tests\SchemaCheckTestTrait;
use Drupal\config_test\TestInstallStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that test views provided by all modules match schema.
 *
 * @group config
 */
class TestViewsTest extends KernelTestBase {

  use SchemaCheckTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views_test_data'];

  /**
   * Tests default configuration data type.
   *
   * @group legacy
   */
  public function testDefaultConfig() {
    $this->expectDeprecation("The 'vid' key in 'views.filter.taxonomy_index_tid' config schema is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Update your view to use the 'vids' key instead. See https://www.drupal.org/node/3162414");
    // Create a typed config manager with access to configuration schema in
    // every module, profile and theme.
    $typed_config = new TypedConfigManager(
      \Drupal::service('config.storage'),
      new TestInstallStorage(InstallStorage::CONFIG_SCHEMA_DIRECTORY),
      \Drupal::service('cache.discovery'),
      \Drupal::service('module_handler'),
      \Drupal::service('class_resolver')
    );

    // Create a configuration storage with access to default configuration in
    // every module, profile and theme.
    $default_config_storage = new TestInstallStorage('test_views');

    foreach ($default_config_storage->listAll() as $config_name) {
      // Skip files provided by the config_schema_test module since that module
      // is explicitly for testing schema.
      if (str_starts_with($config_name, 'config_schema_test')) {
        continue;
      }

      $data = $default_config_storage->read($config_name);
      $this->assertConfigSchema($typed_config, $config_name, $data);
    }
  }

}
