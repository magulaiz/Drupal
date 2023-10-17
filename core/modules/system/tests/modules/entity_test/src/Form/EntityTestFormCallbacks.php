<?php

namespace Drupal\entity_test\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Implements trusted form callbacks for entity_test module.
 */
class EntityTestFormCallbacks {

  /**
   * Implements #validate callback for the entity_test entity form.
   */
  #[TrustedCallback]
  public static function validate(array &$form, FormStateInterface $form_state) {
    $form['#entity_test_form_validate'] = TRUE;
  }

  /**
   * Implements #validate callback for the entity_test entity form.
   */
  #[TrustedCallback]
  public static function check(array &$form, FormStateInterface $form_state) {
    if (!empty($form['#entity_test_form_validate'])) {
      \Drupal::state()->set('entity_test.form.validate.result', TRUE);
    }
  }

}
