<?php

declare(strict_types=1);

namespace Drupal\core_sdc_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * https://www.drupal.org/project/drupal/issues/3494634
 */
class FullComponentWorking extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'core_sdc_form_5';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['component_card'] = [
      '#type' => 'component',
      '#component' => 'core_sdc_form:card',
      'header' => [
        '#markup' => (string) $this->t('Card'),
      ],
      'content' => [
        '#type' => 'component',
        '#component' => 'core_sdc_form:card_body',
        'content' => [
          [
            '#type' => 'component',
            '#component' => 'core_sdc_form:mytextfield',
            '#slots' => [
              'label' => (string) $this->t('My Bootstrap textfield'),
            ],
            '#name' => 'foo',
            '#default_value' => 'default',
          ],
          [
            '#type' => 'component',
            '#component' => 'core_sdc_form:myselect',
            '#slots' => [
              'label' => (string) $this->t('My Bootstrap select'),
            ],
            '#props' => [
              'options' => [
                '1' => $this->t('One'),
                '2' => $this->t('Two'),
                '3' => $this->t('Three'),
              ],
            ],
            '#name' => 'bar',
            '#default_value' => '2',
          ],
          [
            '#type' => 'component',
            '#component' => 'core_sdc_form:myradios',
            '#props' => [
              'options' => [
                '1' => $this->t('One'),
                '2' => $this->t('Two'),
                '3' => $this->t('Three'),
              ],
            ],
            '#name' => 'baz',
            '#default_value' => '3',
          ],
        ],
      ],
      'footer' => [
        '#type' => 'actions',
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
        ],
      ],
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
      'foo',
      'bar',
      'baz',
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
