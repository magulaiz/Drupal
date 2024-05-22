<?php

declare(strict_types=1);

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form for testing #maxlength of confirm_password field..
 *
 * @internal
 *
 * @see todo
 */
class FormTestConfirmPasswordForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'form_test_confirm_password_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['pass'] = [
      '#type' => 'password_confirm',
      '#title' => 'Enter password',
      '#maxlength' => 15,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
