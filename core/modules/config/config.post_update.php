<?php

/**
 * @file
 * Post update functions for config.
 */

/**
 * Load the config and save to trigger event listener.
 *
 * @see \Drupal\config\EventSubscriber\MenuParentUpdate
 */
function config_post_update_set_menu_parent_value_to_null(): void {
  $config = \Drupal::configFactory()->getEditable('core.menu.static_menu_link_overrides');
  $config->save();
}
