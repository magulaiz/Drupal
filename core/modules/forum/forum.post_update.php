<?php

/**
 * @file
 * Contains post update functions.
 */

/**
 * Implements hook_removed_post_updates().
 */
function forum_removed_post_updates() {
  return [
    'forum_post_update_recreate_forum_index_rows' => '11.0.0',
    'help_post_update_help_topics_uninstall' => '11.0.0',
    'help_post_update_add_permissions_to_roles' => '11.0.0',
  ];
}
