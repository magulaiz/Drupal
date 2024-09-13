<?php

/**
 * @file
 * Post update functions for the Navigation module.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\user\RoleInterface;

/**
 * Grants navigation specific permission to roles with access to any layout.
 */
function navigation_post_update_update_permissions(array &$sandbox) {
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'user_role', function (RoleInterface $role) {
    $needs_save = FALSE;
    if ($role->hasPermission('configure any layout')) {
      $role->grantPermission('configure navigation layout');
      $needs_save = TRUE;
    }
    if ($role->hasPermission('administer navigation_block')) {
      $role->revokePermission('administer navigation_block');
      $role->grantPermission('configure navigation layout');
      $needs_save = TRUE;
    }
    return $needs_save;
  });
}

/**
 * Defines the values for the default logo dimensions.
 */
function navigation_post_update_set_logo_dimensions_default(array &$sandbox) {
  $settings = \Drupal::configFactory()->getEditable('navigation.settings');
  $settings->set('logo_height', 40)
    ->set('logo_width', 40);
  if (is_array($settings->get('logo_managed'))) {
    $settings->set('logo_managed', current($settings->get('logo_managed')));
  }
  $settings->save();
}

/**
 * Reorganizes the values for the logo settings.
 */
function navigation_post_update_reorganize_logo_settings2(array &$sandbox) {
  $settings = \Drupal::configFactory()->getEditable('navigation.settings');
  $settings->setData([
    'logo' => [
      'provider' => $settings->get('logo_provider'),
      'managed' => $settings->get('logo_managed'),
      'max_filesize' => $settings->get('logo_max_filesize'),
      'height' => $settings->get('logo_height'),
      'width' => $settings->get('logo_width'),
    ],
  ]);
  $settings->save();
}
