<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\shortcut\ShortcutSetInterface;
use Drupal\shortcut\ShortcutSetStorage as CoreShortcutSetStorage;

/**
 * The MongoDB implementation of \Drupal\shortcut\ShortcutStorage.
 */
class ShortcutSetStorage extends CoreShortcutSetStorage {

  /**
   * {@inheritdoc}
   */
  public function countAssignedUsers(ShortcutSetInterface $shortcut_set) {
    return db_select('shortcut_set_users', 's')
      ->countQuery()
      ->condition('set_name', $shortcut_set->id())
      ->execute()
      ->fetchField();
  }

}
