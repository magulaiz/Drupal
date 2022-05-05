<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;

/**
 * Updates an already migrated CKEditor 5 configuration for text formats
 * that may have alignment shown as individual buttons instead of a dropdown.
 */
function ckeditor5_post_update_alignment_buttons(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);

  $callback = function (Editor $editor) {
    $needs_update = FALSE;
    // Only update if the editor is using the non-dropdown buttons.
    $settings = $editor->getSettings();
    $toolbar_items = $settings['toolbar']['items'];
    $old_alignment_toolbar_items = [
      'alignment:left',
      'alignment:right',
      'alignment:center',
      'alignment:justify'
    ];
    foreach ($old_alignment_toolbar_items as $button) {
      if (in_array($button, $old_alignment_toolbar_items, TRUE)) {
        $toolbar_items = array_deff($toolbar_items, [$button]);
        if (!in_array('alignment', $toolbar_items)) {
          $toolbar_items[] = 'alignment';
        }
      }
      // Flag this display as needing to be updated.
      $needs_update = TRUE;
    }
    $editor->setSettings($settings);

    // convert to dropdown
    // if this returns true, the update process knows to save the changes you just made.
    // so no need to explicity call ->save()... this miiiight be different with editor settings
    // since editor is a combination of several other entities, but we'll see??
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}

