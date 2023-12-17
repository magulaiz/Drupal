<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Builds a simple form to test the #group property on #type 'container'.
 *
 * @internal
 */
class FormTestDetailsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_test_details_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['meta'] = [
      '#type' => 'details',
      '#title' => 'Details element',
      '#open' => TRUE,
    ];
    $form['submit'] = ['#type' => 'submit', '#value' => 'Submit'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->setErrorByName('meta', 'I am an error on the details element.');
  }

  /**
   * {@inheritdoc}
   */
  #[TrustedCallback]
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
