<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormStateInterface;

class InvalidConfigTargetForm extends ConfigFormBase {

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
    return 'form_test_invalid_config_target_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['bananas'] = [
      '#type' => 'textfield',
      '#title' => t('Bananas'),
      '#config_target' => new ConfigTarget('form_test.object',
        [
          'favorite_vegetable',
          'nemesis_vegetable',
        ],
        // phpcs:disable
        // This "toConfig" callable is not allowed to throw this exception
        // because it is a config target targeting a single property.
        toConfig: fn () => throw new \OutOfBoundsException(),
      ),
    ];
    return parent::buildForm($form, $form_state);
  }

}
