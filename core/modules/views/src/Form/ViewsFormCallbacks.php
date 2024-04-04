<?php

namespace Drupal\views\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted form callbacks for views.
 */
class ViewsFormCallbacks {

  /**
   * Implements #element_validate callback for query tags.
   */
  #[TrustedCallback]
  public static function elementValidateTags(array $element, FormStateInterface $form_state) {
    $values = array_map('trim', explode(',', $element['#value']));
    foreach ($values as $value) {
      if (preg_match("/[^a-z_]/", $value)) {
        $form_state->setError($element, t('The query tags may only contain lower-case alphabetical characters and underscores.'));
        return;
      }
    }
  }

}
