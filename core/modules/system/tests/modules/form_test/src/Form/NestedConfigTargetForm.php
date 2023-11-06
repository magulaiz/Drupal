<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class NestedConfigTargetForm extends ConfigFormBase {

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
      '#input' => TRUE,
      '#title' => t('Favorite fruits'),
      '#config_target' => 'form_test.object:favorite_fruits',
    ];
    $form['favorites']['first'] = [
      '#type' => 'textfield',
      '#title' => t('First choice'),
      '#default_value' => 'Mango',
    ];
    $form['favorites']['second'] = [
      '#type' => 'textfield',
      '#title' => t('Second choice'),
      '#default_value' => 'Orange',
    ];
    return parent::buildForm($form, $form_state);
  }

}
