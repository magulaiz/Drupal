<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Form constructor for testing invalid #type 'range' elements.
 *
 * @internal
 */
class FormTestRangeInvalidForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_test_range_invalid';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['minmax'] = [
      '#type' => 'range',
      '#min' => 10,
      '#max' => 5,
      '#title' => 'Invalid range',
      '#description' => 'Minimum greater than maximum.',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
