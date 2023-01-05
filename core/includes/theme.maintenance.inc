<?php

/**
 * @file
 * Theming for maintenance pages.
 */

@trigger_error("theme.maintenance.inc file is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use appropriate services from the core instead. See https://www.drupal.org/node/3058979", E_USER_DEPRECATED);

/**
 * Sets up the theming system for maintenance page.
 *
 * Used for site installs, updates and when the site is in maintenance mode.
 * It also applies when the database is unavailable or bootstrap was not
 * complete. Claro is always used for the initial install and update
 * operations, but this can be overridden by setting a "maintenance_theme" key
 * in the $settings variable in settings.php.
 *
 * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
 *   \Drupal::service('maintenance_mode')->setTheme() instead.
 *
 * @see https://www.drupal.org/node/3058979
 */
function _drupal_maintenance_theme() {
  @trigger_error("_drupal_maintenance_theme() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal::service('maintenance_mode')->setTheme() instead. See https://www.drupal.org/node/3058979", E_USER_DEPRECATED);
  \Drupal::service('maintenance_mode')->setTheme();
}
