<?php

/**
 * @file
 */

use Drupal\Core\Hook\LegacyHook;
use Drupal\user\Hook\UserViewsHooks;

/**
 * @file
 * Provide views data for user.module.
 */

/**
 * Implements hook_views_plugins_argument_validator_alter().
 */
#[LegacyHook]
function user_views_plugins_argument_validator_alter(array &$plugins) {
  \Drupal::service(UserViewsHooks::CLASS)->userViewsPluginsArgumentValidatorAlter($plugins);
}
