<?php

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'options_dynamic' widget.
 *
 * @FieldWidget(
 *   id = "options_dynamic",
 *   label = @Translation("Dynamic options"),
 *   field_types = {
 *     "boolean",
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *   },
 *   multiple_values = TRUE
 * )
 */
class OptionsDynamicWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'select_threshold' => 7,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['select_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Select threshold'),
      '#description' => $this->t('Number of available options after which the select is used.'),
      '#default_value' => $this->getSetting('select_threshold'),
      '#min' => 2,
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Select threshold: @threshold options.', [
      '@threshold' => $this->getSetting('select_threshold'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $options = $this->getOptions($items->getEntity());
    $select_threshold = $this->getSetting('select_threshold');
    $select_threshold = isset($options['_none']) ? $select_threshold + 1 : $select_threshold;
    $selected = $this->getSelectedOptions($items);

    // Customizations specific for Check boxes/radio buttons.
    if (count($options) <= $select_threshold) {
      if (isset($options['_none']) && ($this->required || $this->multiple)) {
        $select_threshold--;
        unset($options['_none']);
      }

      if (!$this->required && !$this->multiple) {
        $options['_none'] = $this->t('N/A');
      }

      // If required and there is one single option, preselect it.
      if ($this->required && count($options) == 1) {
        reset($options);
        $selected = [key($options)];
      }

      if (!$this->multiple) {
        $selected = $selected ? reset($selected) : NULL;
      }
    }

    $element += [
      '#type' => 'dynamic_options',
      '#options' => $options,
      '#default_value' => $selected,
      '#select_threshold' => $select_threshold,
      '#multiple' => $this->multiple,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function sanitizeLabel(&$label) {
    // Select form inputs allow unencoded HTML entities, but no HTML tags.
    $label = Html::decodeEntities(strip_tags($label));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if ($this->multiple) {
      // Multiple select: add a 'none' option for non-required fields.
      if (!$this->required) {
        return $this->t('- None -');
      }
    }
    else {
      // Single select: add a 'none' option for non-required fields,
      // and a 'select a value' option for required fields that do not come
      // with a value selected.
      if (!$this->required) {
        return $this->t('- None -');
      }
      if (!$this->has_value) {
        return $this->t('- Select a value -');
      }
    }
  }

}
