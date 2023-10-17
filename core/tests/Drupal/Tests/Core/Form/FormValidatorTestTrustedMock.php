<?php

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Stub class to implement TrustedCallbackInterface.
 */
class FormValidatorTestTrustedMock {

  /**
   * Implements #validate callback for the FormValidatorTest class.
   */
  #[TrustedCallback]
  public static function validateHandler(array &$form, FormStateInterface &$form_state) {
    if (isset($form['#form_validator_test_validate_handler'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormValidatorTestTrustedMock::validateHandler called more than once.');
    }
    $form['#form_validator_test_validate_handler'] = TRUE;
  }

  /**
   * Implements #validate callback for the FormValidatorTest class.
   */
  #[TrustedCallback]
  public static function hashValidate(array &$form, FormStateInterface &$form_state) {
    if (isset($form['#form_validator_test_validate_hash'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormValidatorTestTrustedMock::hashValidate called more than once.');
    }
    $form['#form_validator_test_validate_hash'] = TRUE;
  }

  /**
   * Implements #element_validate callback for the FormValidatorTest class.
   */
  #[TrustedCallback]
  public static function elementValidate(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (isset($element['#element_validate_test'])) {
      throw new ExpectationFailedException('\Drupal\Tests\Core\Form\FormValidatorTestTrustedMock::elementValidate called more than once.');
    }
    $element['#element_validate_test'] = TRUE;
  }

}
