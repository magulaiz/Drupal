<?php

/**
 * @file
 * Post-update functions for Image.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\Core\Field\FieldConfigInterface;

/**
 * Implements hook_removed_post_updates().
 */
function image_removed_post_updates() {
  return [
    'image_post_update_image_style_dependencies' => '9.0.0',
    'image_post_update_scale_and_crop_effect_add_anchor' => '9.0.0',
    'image_post_update_image_loading_attribute' => '10.0.0',
  ];
}

/**
 * Adds new resize_policy setting to existing image fields.
 */
function image_post_update_add_resize_policy(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);
  $config_entity_updater->update($sandbox, 'field_config', function (FieldConfigInterface $field): bool {
    return $field->getType() === 'image';
  });
}
