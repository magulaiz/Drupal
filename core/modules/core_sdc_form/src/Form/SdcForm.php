<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class SdcForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'core_sdc_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
//    $form['test'] = [
//      '#type' => 'textfield',
//      '#title' => $this->t('Normal form element'),
//    ];

    $form['component'] =  [
      '#type' => 'component',
      '#component' => 'navigation:badge',
      '#slots' => [
        'label' => [
          'form_element_in_component' => [
            '#type' => 'textfield',
            '#title' => $this->t('form element in component'),
          ],
        ],
      ],
//      'form_element_component_children' => [
//        '#type' => 'textfield',
//        '#title' => $this->t('form element component children'),
//      ],
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
    dpm(array_keys($values));
    $keys = [
      'test',
      'form_element_in_component',
      'form_element_component_children',
    ];
    foreach ($keys as $key) {
      if (isset($values[$key])) {
        dpm($values[$key], $key);
      }
    }
  }

}
