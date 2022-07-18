<?php

/**
 * @file
 * Post update functions for Media.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\media\MediaConfigUpdater;

/**
 * Implements hook_removed_post_updates().
 */
function media_removed_post_updates() {
  return [
    'media_post_update_collection_route' => '9.0.0',
    'media_post_update_storage_handler' => '9.0.0',
    'media_post_update_enable_standalone_url' => '9.0.0',
    'media_post_update_add_status_extra_filter' => '9.0.0',
  ];
}

/**
 * Updates stale references to Drupal\media\Entity\Media::getCurrentUserId.
 */
function media_post_update_modify_base_field_author_override() {
  $uid_fields = \Drupal::entityTypeManager()
    ->getStorage('base_field_override')
    ->getQuery()
    ->condition('entity_type', 'media')
    ->condition('field_name', 'uid')
    ->condition('default_value_callback', 'Drupal\media\Entity\Media::getCurrentUserId')
    ->execute();
  foreach (BaseFieldOverride::loadMultiple($uid_fields) as $base_field_override) {
    $base_field_override->setDefaultValueCallback('Drupal\media\Entity\Media::getDefaultEntityOwner')->save();
  }
}

/**
 * Add the oembed iframe loading attribute setting to field formatter instances.
 */
function media_post_update_oembed_loading_attribute(array &$sandbox = NULL): void {
  $media_config_updater = \Drupal::classResolver(MediaConfigUpdater::class);
  assert($media_config_updater instanceof MediaConfigUpdater);
  $media_config_updater->setDeprecationsEnabled(TRUE);
  \Drupal::classResolver(ConfigEntityUpdater::class)->update($sandbox, 'entity_view_display', function (EntityViewDisplayInterface $view_display) use ($media_config_updater): bool {
    return $media_config_updater->processOembedEagerLoadField($view_display);
  });
}
