<?php

namespace Drupal\comment\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted form callbacks for Comment module.
 */
class CommentTrustedFormCallbacks {

  /**
   * Used as #process callback to remove comment type field option.
   */
  #[TrustedCallback]
  public static function newStorageType(array $element, FormStateInterface &$form_state, array $form) {
    foreach ($element as $key => $value) {
      if (isset($value['radio']['#return_value']) && $value['radio']['#return_value'] === 'comment') {
        // You cannot use comment fields on entity types with non-integer IDs.
        unset($element[$key]);
      }
    }

    return $element;
  }

}
