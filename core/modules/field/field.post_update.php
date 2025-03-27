<?php

/**
 * @file
 * Post update functions for Field module.
 */

/**
 * Implements hook_removed_post_updates().
 */
function field_removed_post_updates() {
  return [
    'field_post_update_save_custom_storage_property' => '9.0.0',
    'field_post_update_entity_reference_handler_setting' => '9.0.0',
    'field_post_update_email_widget_size_setting' => '9.0.0',
    'field_post_update_remove_handler_submit_setting' => '9.0.0',
  ];
}

/**
 * Resave all entity view/form displays with recalculated dependencies.
 */
function field_post_update_resave_all_entity_view_or_form_displays(): void {

  $entity_type_manager = \Drupal::entityTypeManager();

  /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity_form_display */
  foreach ($entity_type_manager->getStorage('entity_form_display')->loadMultiple() as $entity_form_display) {
    $entity_form_display->save();
  }

  /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $entity_view_display */
  foreach ($entity_type_manager->getStorage('entity_view_display')->loadMultiple() as $entity_view_display) {
    $entity_view_display->save();
  }
}
