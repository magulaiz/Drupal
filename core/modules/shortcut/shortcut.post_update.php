<?php

/**
 * @file
 * Post-update functions for the Shortcut module.
 */

use Drupal\shortcut\Entity\Shortcut;

/**
 * Fix empty shortcut titles.
 */
function shortcut_post_update_fix_empty_titles(): void {
  $ids = \Drupal::entityQuery('shortcut')
    ->accessCheck(FALSE)
    ->execute();
  $shortcuts = Shortcut::loadMultiple($ids);

  if (!empty($shortcuts)) {
    foreach ($shortcuts as $shortcut) {
      if (empty($shortcut->getTitle())) {
        $shortcut->setTitle('(' . t('empty', [], ['langcode' => $shortcut->language()->getId()]) . ')');
        $shortcut->save();
      }
    }
  }
}
