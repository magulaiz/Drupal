<?php

/**
 * @file
 * Post update functions for menu_ui.
 */

/**
 * Adds link_by_default config in node.type.*.third_party.menu_ui settings.
 */
function menu_ui_post_update_add_link_by_default() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('node.type.') as $node_type_config_name) {
    if (!str_contains($node_type_config_name, 'third_party.menu_ui')) {
      continue;
    }
    $node_type_third_party_menu_ui = $config_factory->getEditable($node_type_config_name);
    $node_type_third_party_menu_ui_settings = $node_type_third_party_menu_ui->get('settings');

    $node_type_third_party_menu_ui_settings['link_by_default'] = FALSE;
    $node_type_third_party_menu_ui->set('settings', $node_type_third_party_menu_ui_settings);
    $node_type_third_party_menu_ui->save(TRUE);
  }
}
