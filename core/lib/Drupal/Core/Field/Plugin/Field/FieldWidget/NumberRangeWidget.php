<?php

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'range number' widget.
 *
 * @FieldWidget(
 *   id = "number_range",
 *   label = @Translation("Range field"),
 *   field_types = {
 *     "integer",
 *     "decimal",
 *     "float"
 *   }
 * )
 */
class NumberRangeWidget extends NumberWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'output' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['output'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display current value'),
      '#default_value' => $this->getSetting('output'),
      '#description' => $this->t('Show the current value next to the slider.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element_array = parent::formElement($items, $delta, $element, $form, $form_state);
    $element_array['value']['#type'] = 'range';
    return $element_array;
  }

}
