<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Form constructor for testing #type 'file' elements.
 *
 * @internal
 */
class FormTestFileForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_test_file';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file'] = [
      '#type' => 'file',
      '#multiple' => TRUE,
      '#attributes' => [
        'class' => ['some-class'],
      ],
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
