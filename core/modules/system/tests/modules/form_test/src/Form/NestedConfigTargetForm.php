<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormStateInterface;

class NestedConfigTargetForm extends TreeConfigTargetForm {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['form_test.object'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_test_nested_config_target_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['favorites'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#tree' => TRUE,
      '#title' => t('Favorite fruits'),
    ];
    $form['favorites']['first'] = [
      '#type' => 'textfield',
      '#title' => t('First choice'),
      '#config_target' => new ConfigTarget(
        'form_test.object',
        'favorite_fruits',
        fromConfig: fn (?array $favorite_fruits): string => $favorite_fruits[0] ?? 'Mango',
        toConfig: fn (string $first, FormStateInterface $form_state): array => [
          0 => $first,
          1 => $form_state->getValue(['favorites', 'second']),
        ],
      ),
    ];
    $form['favorites']['second'] = [
      '#type' => 'textfield',
      '#title' => t('Second choice'),
      '#config_target' => new ConfigTarget(
        'form_test.object',
        'favorite_fruits.1',
        fn (?string $second_favorite_fruit) : string => $second_favorite_fruit ?? 'Orange',
        // phpcs:disable
        // The "toConfig" callable for the first choice sets all choices.
        fn () => throw new \OutOfBoundsException(),
      ),
    ];
    return parent::buildForm($form, $form_state);
  }

}
