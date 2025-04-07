<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class FormInComponent extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'core_sdc_form_1';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['form_in_component'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Textfield in component'),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($form_state->getValue('form_in_component'));
  }

}
