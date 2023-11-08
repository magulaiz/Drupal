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
        fromConfig: static::class . '::getFirstIfExists',
        toConfig: static::class . '::toFavoriteFruits',
      ),
    ];
    $form['favorites']['second'] = [
      '#type' => 'textfield',
      '#title' => t('Second choice'),
      '#config_target' => new ConfigTarget(
        'form_test.object',
        'favorite_fruits.1',
        toConfig: static::class . '::nothing',
      ),
      '#states' => [
        // @todo hide this unless the first favorite is not empty
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  public static function getFirstIfExists(?array $favorite_fruits) : ?string {
    $favorite_fruits = $favorite_fruits ?? [];
    return array_key_exists(0, $favorite_fruits) ? $favorite_fruits[0] : 'Mango';
  }

  public static function toFavoriteFruits(FormStateInterface $form_state, string $first) : array {
    $favorites = [];
    if (!empty($first)) {
      $favorites[] = $first;
    }
    $second = $form_state->getValue(['favorites', 'second']);
    if (!empty($second)) {
      $favorites[] = $second;
    }
    return $favorites;
  }

  public static function nothing() : array {
    throw new \OutOfBoundsException();
  }

}
