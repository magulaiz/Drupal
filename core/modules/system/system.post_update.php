<?php

/**
 * @file
 * Post update functions for System.
 */

/**
 * Implements hook_removed_post_updates().
 */
function system_removed_post_updates() {
  return [
    'system_post_update_recalculate_configuration_entity_dependencies' => '9.0.0',
    'system_post_update_add_region_to_entity_displays' => '9.0.0',
    'system_post_update_hashes_clear_cache' => '9.0.0',
    'system_post_update_timestamp_plugins' => '9.0.0',
    'system_post_update_classy_message_library' => '9.0.0',
    'system_post_update_field_type_plugins' => '9.0.0',
    'system_post_update_field_formatter_entity_schema' => '9.0.0',
    'system_post_update_fix_jquery_extend' => '9.0.0',
    'system_post_update_change_action_plugins' => '9.0.0',
    'system_post_update_change_delete_action_plugins' => '9.0.0',
    'system_post_update_language_item_callback' => '9.0.0',
    'system_post_update_extra_fields' => '9.0.0',
    'system_post_update_states_clear_cache' => '9.0.0',
    'system_post_update_add_expand_all_items_key_in_system_menu_block' => '9.0.0',
    'system_post_update_clear_menu_cache' => '9.0.0',
    'system_post_update_layout_plugin_schema_change' => '9.0.0',
    'system_post_update_entity_reference_autocomplete_match_limit' => '9.0.0',
    'system_post_update_extra_fields_form_display' => '10.0.0',
    'system_post_update_uninstall_simpletest' => '10.0.0',
    'system_post_update_uninstall_entity_reference_module' => '10.0.0',
    'system_post_update_entity_revision_metadata_bc_cleanup' => '10.0.0',
    'system_post_update_uninstall_classy' => '10.0.0',
    'system_post_update_uninstall_stable' => '10.0.0',
    'system_post_update_claro_dropbutton_variants' => '10.0.0',
    'system_post_update_schema_version_int' => '10.0.0',
    'system_post_update_delete_rss_settings' => '10.0.0',
    'system_post_update_remove_key_value_expire_all_index' => '10.0.0',
    'system_post_update_service_advisory_settings' => '10.0.0',
    'system_post_update_delete_authorize_settings' => '10.0.0',
    'system_post_update_sort_all_config' => '10.0.0',
    'system_post_update_enable_provider_database_driver' => '10.0.0',
  ];
}

/**
 * Add new menu linkset endpoint setting.
 */
function system_post_update_linkset_settings() {
  $config = \Drupal::configFactory()->getEditable('system.feature_flags');
  $config->set('linkset_endpoint', FALSE)->save();
}

/**
 * Re-save all configuration to ensure sorting.
 */
function system_post_update_resave_configuration(&$sandbox = NULL) {
  $config_factory = \Drupal::configFactory();
  if (!isset($sandbox['config_names'])) {
    $sandbox['config_names'] = $config_factory->listAll();
    $sandbox['count'] = count($sandbox['config_names']);
  }

  $count = 0;
  foreach ($sandbox['config_names'] as $key => $config_name) {
    // Resave configuration to ensure any new sorting is applied.
    $config_factory->getEditable($config_name)->save();

    unset($sandbox['config_names'][$key]);
    $count++;
    // Do 50 at a time.
    if ($count == 50) {
      break;
    }
  }

  $sandbox['#finished'] = empty($sandbox['config_names']) ? 1 : ($sandbox['count'] - count($sandbox['config_names'])) / $sandbox['count'];
  return t('Configuration resaved');
}
