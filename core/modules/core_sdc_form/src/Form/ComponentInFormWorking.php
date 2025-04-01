<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class ComponentInFormWorking extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'core_sdc_form_3';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['normal'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Normal form element'),
    ];

    $form['component_card'] = [
      '#type' => 'component',
      '#component' => 'core_sdc_form:card',
//      No more put slots in #slots.
//      '#slots' => [
      'header' => [
        '#markup' => (string) $this->t('Card'),
      ],
      'content' => [
        '#type' => 'component',
        '#component' => 'core_sdc_form:card_body',
        'content' => [
          [
            '#type' => 'textfield',
            '#title' => $this->t('Textfield in content'),
            '#name' => 'content',
          ],
          [
            '#type' => 'select',
            '#title' => $this->t('Select in content'),
            '#name' => 'content_select',
            '#options' => [
              'option_1' => $this->t('Option 1'),
              'option_2' => $this->t('Option 2'),
            ],
            '#empty_option' => $this->t('Empty option'),
          ],
        ],
      ],
      'footer' => [
        '#type' => 'textfield',
        '#title' => $this->t('Textfield in footer'),
        '#name' => 'footer',
      ],
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
    $keys = [
      'normal',
      'content',
      'content_select',
      'footer',
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
