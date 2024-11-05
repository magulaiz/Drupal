<?php

declare(strict_types=1);

namespace Drupal\config_test\Hook;

use Drupal\config_test\Entity\ConfigTest;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for config_test.
 */
class ConfigTestHooksHooks {

  /**
   * Implements hook_config_test_load().
   */
  #[Hook('config_test_load')]
  public function configTestLoad() {
    $GLOBALS['hook_config_test']['load'] = 'config_test_config_test_load';
  }

  /**
   * Implements hook_ENTITY_TYPE_create() for 'config_test'.
   */
  #[Hook('config_test_create')]
  public function configTestCreate(ConfigTest $config_test) {
    if (\Drupal::state()->get('config_test.prepopulate')) {
      $config_test->set('foo', 'baz');
    }
    _config_test_update_is_syncing_store('create', $config_test);
  }

  /**
   * Implements hook_config_test_presave().
   */
  #[Hook('config_test_presave')]
  public function configTestPresave(ConfigTest $config_test) {
    $GLOBALS['hook_config_test']['presave'] = 'config_test_config_test_presave';
    _config_test_update_is_syncing_store('presave', $config_test);
  }

  /**
   * Implements hook_config_test_insert().
   */
  #[Hook('config_test_insert')]
  public function configTestInsert(ConfigTest $config_test) {
    $GLOBALS['hook_config_test']['insert'] = 'config_test_config_test_insert';
    _config_test_update_is_syncing_store('insert', $config_test);
  }

  /**
   * Implements hook_config_test_update().
   */
  #[Hook('config_test_update')]
  public function configTestUpdate(ConfigTest $config_test) {
    $GLOBALS['hook_config_test']['update'] = 'config_test_config_test_update';
    _config_test_update_is_syncing_store('update', $config_test);
  }

  /**
   * Implements hook_config_test_predelete().
   */
  #[Hook('config_test_predelete')]
  public function configTestPredelete(ConfigTest $config_test) {
    $GLOBALS['hook_config_test']['predelete'] = 'config_test_config_test_predelete';
    _config_test_update_is_syncing_store('predelete', $config_test);
  }

  /**
   * Implements hook_config_test_delete().
   */
  #[Hook('config_test_delete')]
  public function configTestDelete(ConfigTest $config_test) {
    $GLOBALS['hook_config_test']['delete'] = 'config_test_config_test_delete';
    _config_test_update_is_syncing_store('delete', $config_test);
  }

}
