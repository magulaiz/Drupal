<?php
namespace Drupal\form_test\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
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
      '#default_value' => 'Mango',
    ];
    $form['favorites']['second'] = [
      '#type' => 'textfield',
      '#title' => t('Second choice'),
      '#default_value' => 'Orange',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected static function copyFormValuesToConfig(Config $config, FormStateInterface $form_state): void {
    // The 1:1 things can be handled by the base class.
    parent::copyFormValuesToConfig($config, $form_state);

    // Not every config property is mapped 1:1 to a form element.
    $config->set('favorite_fruits', [
      0 => $form_state->getValue(['favorites', 'first']),
      1 => $form_state->getValue(['favorites', 'second']),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected static function mapConfigKeyToFormElementName(string $config_name, string $key) : string {
    if ($key === 'favorite_fruits.0') {
      return 'favorites][first';
    }
    if ($key === 'favorite_fruits.1') {
      return 'favorites][second';
    }
    return '';
  }

}
