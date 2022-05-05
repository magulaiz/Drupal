<?php

/**
 * @file
 * Post update functions for CKEditor 5.
 */

use Drupal\Core\Config\Entity\ConfigEntityUpdater;
use Drupal\editor\Entity\Editor;

/**
 * Updates if an already migrated CKEditor 5 configuration for text formats
 * has alignment shown as individual buttons instead of a dropdown.
 *
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function ckeditor5_post_update_alignment_buttons(&$sandbox = []) {
  $config_entity_updater = \Drupal::classResolver(ConfigEntityUpdater::class);

  $callback = function (Editor $editor) {
    $needs_update = FALSE;
    // Only update if the editor is using the non-dropdown buttons.
    $settings = $editor->getSettings();
    $old_alignment_buttons_to_types = [
      'alignment:left' => 'left',
      'alignment:right' => 'right',
      'alignment:center' => 'center',
      'alignment:justify' => 'justify',
    ];
    if (is_array($settings['toolbar']['items'])) {
      foreach ($old_alignment_buttons_to_types as $button => $type) {
        if (in_array($button, $settings['toolbar']['items'])) {
          $settings['toolbar']['items'] = array_diff($settings['toolbar']['items'], [$button]);
          $settings['config']['alignment']['options'][] = ['name' => $type, 'className' => $button];
          if (!in_array('alignment', $settings['toolbar']['items'])) {
            $settings['toolbar']['items'][] = 'alignment';
          }
        }
        // Flag this display as needing to be updated.
        $needs_update = TRUE;
      }
      $editor->setSettings($settings);
    }
    return $needs_update;
  };

  $config_entity_updater->update($sandbox, 'editor', $callback);
}

