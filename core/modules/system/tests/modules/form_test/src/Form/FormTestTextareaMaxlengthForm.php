<?php

declare(strict_types=1);

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Form for testing textarea maxlength properties.
 *
 * @internal
 */
class FormTestTextareaMaxlengthForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_test_textarea_maxlength_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['textarea'] = [
      '#type' => 'textarea',
      '#title' => 'Textarea with maxlength',
      '#maxlength' => 20,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setResponse(new JsonResponse($form_state->getValues()));
  }

}
