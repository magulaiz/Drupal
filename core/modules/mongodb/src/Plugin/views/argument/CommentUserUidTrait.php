<?php

namespace Drupal\mongodb\Plugin\views\argument;

/**
 * Trait for overriding the \Drupal\comment\Plugin\views\argument\UserUid class.
 */
trait CommentUserUidTrait {

  /**
   * {@inheritdoc}
   */
  public function title() {
    if (!$this->argument) {
      $title = \Drupal::config('user.settings')->get('anonymous');
    }
    else {
      $user_translations = $this->database->select('users', 'u')
        ->fields('u', ['user_translations'])
        ->condition('uid', (int) $this->argument)
        ->execute()
        ->fetchField();
      $title = '';
      if (!empty($user_translations) && is_array($user_translations)) {
        foreach ($user_translations as $user_translation) {
          if (isset($user_translation['default_langcode']) && $user_translation['default_langcode'] && isset($user_translation['name'])) {
            $title = $user_translation['name'];
          }
        }
      }
    }
    if (empty($title)) {
      return $this->t('No user');
    }

    return $title;
  }

}
