<?php

namespace Drupal\Core\Render\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for double-input of passwords.
 *
 * Formats as a pair of password fields, which do not validate unless the two
 * entered passwords match.
 *
 * Properties:
 * - #size: The size of the input element in characters.
 *
 * Usage example:
 * @code
 * $form['pass'] = array(
 *   '#type' => 'password_confirm',
 *   '#title' => $this->t('Password'),
 *   '#size' => 25,
 * );
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Password
 *
 * @FormElement("password_confirm")
 */
class PasswordConfirm extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#markup' => '',
      '#process' => [
        [$class, 'processPasswordConfirm'],
      ],
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      $element += ['#default_value' => []];
      return $element['#default_value'] + ['pass1' => '', 'pass2' => ''];
    }

    $value = '';
    if (isset($input['pass1']) && is_scalar($input['pass1'])) {
      $value = (string) $input['pass1'];
    }

    return $value;
  }

  /**
   * Expand a password_confirm field into two text boxes.
   */
  public static function processPasswordConfirm(&$element, FormStateInterface $form_state, &$complete_form) {

    $element['pass1'] = [
      '#type' => 'password',
      '#title' => t('Password'),
      '#required' => $element['#required'],
      '#attributes' => [
        'class' => ['password-field', 'js-password-field'],
        'autocomplete' => ['new-password'],
      ],
      '#error_no_message' => TRUE,
    ];
    $element['pass2'] = [
      '#type' => 'password',
      '#title' => t('Confirm password'),
      '#required' => $element['#required'],
      '#attributes' => [
        'class' => ['password-confirm', 'js-password-confirm'],
        'autocomplete' => ['new-password'],
      ],
      '#error_no_message' => TRUE,
    ];
    $element['#element_validate'] = [[static::class, 'validatePasswordConfirm']];
    $element['#tree'] = TRUE;

    if (isset($element['#size'])) {
      $element['pass1']['#size'] = $element['pass2']['#size'] = $element['#size'];
    }

    if (isset($element['#maxlength'])) {
      $element['pass1']['#maxlength'] = $element['pass2']['#maxlength'] = $element['#maxlength'];
    }

    return $element;
  }

  /**
   * Validates a password_confirm element.
   */
  public static function validatePasswordConfirm(&$element, FormStateInterface $form_state, &$complete_form) {
    $pass1 = trim($form_state->getValue('password')['pass1']);
    $pass2 = trim($form_state->getValue('password')['pass2']);
    if (strlen($pass1) > 0 || strlen($pass2) > 0) {
      if (strcmp($pass1, $pass2)) {
        $form_state->setError($element, t('The specified passwords do not match.'));
      }
    }
    elseif ($element['#required'] && $form_state->getUserInput()) {
      $form_state->setError($element, t('Password field is required.'));
    }

    // Password field must be converted from a two-element array into a single
    // string regardless of validation results.
    $form_state->setValueForElement($element['pass1'], NULL);
    $form_state->setValueForElement($element['pass2'], NULL);
    $form_state->setValueForElement($element, $pass1);

    return $element;
  }

}
