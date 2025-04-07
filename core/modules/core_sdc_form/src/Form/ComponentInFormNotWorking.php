<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class ComponentInFormNotWorking extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'core_sdc_form_2';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['normal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Normal form element'),
    ];

    $form['component'] =  [
      '#type' => 'component',
      '#component' => 'core_sdc_form:accordion',
      '#slots' => [
        'title' => (string) $this->t('Form in component accordion'),
        'content' => [
          'form_element_in_component' => [
            '#type' => 'textfield',
            '#title' => $this->t('Form element in component'),
          ],
        ],
      ],
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
    $values = $form_state->getValues();
    $keys = [
      'normal',
      'component',
      'form_element_in_component',
    ];
    foreach ($keys as $key) {
      if (isset($values[$key])) {
        $this->messenger()->addStatus($this->t('@key: @value', [
          '@key' => $key,
          '@value' => $values[$key],
        ]));
      }
    }
  }

}
