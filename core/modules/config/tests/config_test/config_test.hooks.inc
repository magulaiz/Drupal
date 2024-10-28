<?php

/**
 * @file
 * Fake third-party hook implementations for ConfigTest entities.
 *
 * Testing the module/hook system is not the purpose of this test helper module.
 * Therefore, this file implements hooks on behalf of config_test module for
 * config_test entity hooks themselves.
 */

declare(strict_types=1);

use Drupal\config_test\Entity\ConfigTest;

/**
 * Helper function for testing hooks during configuration sync.
 *
 * @param string $hook
 *   The fired hook.
 * @param \Drupal\config_test\Entity\ConfigTest $config_test
 *   The ConfigTest entity.
 */
function _config_test_update_is_syncing_store($hook, ConfigTest $config_test) {
  $current_value = \Drupal::state()->get('config_test.store_isSyncing', FALSE);
  if ($current_value !== FALSE) {
    $current_value['global_state::' . $hook] = \Drupal::isConfigSyncing();
    $current_value['entity_state::' . $hook] = $config_test->isSyncing();
    \Drupal::state()->set('config_test.store_isSyncing', $current_value);
  }
}
