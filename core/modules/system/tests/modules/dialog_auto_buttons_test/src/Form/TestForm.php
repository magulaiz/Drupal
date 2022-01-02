<?php

namespace Drupal\dialog_auto_buttons_test\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Provides a form for testing the drupalAutoButtons dialog option.
 */
class TestForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return "dialog_auto_buttons_test_form";
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return [
      'actions' => [
        '#type' => 'actions',
        'submit_1' => [
          '#type' => 'submit',
          '#value' => 'Submit button 1',
        ],
        'submit_2' => [
          '#type' => 'submit',
          '#value' => 'Submit button 2',
        ],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
