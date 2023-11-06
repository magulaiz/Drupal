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
    $form['fruits'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#input' => TRUE,
      '#title' => t('Fruit preferences'),
    ];
    $form['fruits']['favorite'] = [
      '#type' => 'textfield',
      '#title' => t('Favorite'),
      '#default_value' => 'Mango',
      '#config_target' => 'form_test.object:favorite_fruit',
    ];
    $form['fruits']['nemesis'] = [
      '#type' => 'textfield',
      '#title' => t('Nemesis'),
      '#default_value' => 'Orange',
      '#config_target' => 'form_test.object:nemesis_fruit',
    ];
    return parent::buildForm($form, $form_state);
  }

}
