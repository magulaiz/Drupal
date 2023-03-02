<?php

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\DynamicOptions;

/**
 * Plugin implementation of the 'options_dynamic' widget.
 *
 * @FieldWidget(
 *   id = "options_dynamic",
 *   label = @Translation("Checkbox/radios or select"),
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

  protected function formElements() {
    return [
      'buttons' => $this->t('Checkbox/radios'),
      'select' => $this->t('Select'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'form_element' => 'buttons',
      'threshold_enabled' => TRUE,
      'select_threshold' => 7,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['form_element'] = [
      '#type' => 'radios',
      '#title' => $this->t('Default form element'),
      '#options' => $this->formElements(),
      '#default_value' => $this->getSetting('form_element'),
    ];

    $element['threshold_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Switch to select at some point to improve usability.'),
      '#default_value' => $this->getSetting('threshold_enabled'),
      '#states' => [
        'visible' => [':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][form_element]"]' => ['value' => 'buttons']],
      ]
    ];

    $element['select_threshold'] = [
      '#type' => 'number',
      '#title' => $this->t('Switch to Select at'),
      '#description' => $this->t('Use checkboxes or radio buttons up to this many options.'),
      '#default_value' => $this->getSetting('select_threshold'),
      '#min' => 2,
      '#required' => TRUE,
      '#field_suffix' => $this->t('options'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][form_element]"]' => ['value' => 'buttons'],
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][threshold_enabled]"]' => ['checked' => TRUE],
          ],
      ]
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Form element: @element',[
      '@element' => $this->formElements()[$this->getSetting('form_element')]
    ]);
    if ($this->getSetting('form_element') === 'buttons' && $this->getSetting('threshold_enabled')) {
      $summary[] = $this->t('Use checkboxes when there are more than @threshold options.', [
        '@threshold' => $this->getSetting('select_threshold'),
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items);

    if ($this->getSetting('form_element') === 'select') {
      $select_threshold = 1;
    }
    else {
      if ($this->getSetting('threshold_enabled') == 0) {
        $select_threshold = DynamicOptions::FORCE_BUTTONS;
      }
      else {
        $select_threshold = $this->getSetting('select_threshold');
        $select_threshold = isset($options['_none']) ? $select_threshold + 1 : $select_threshold;
      }

      // Customizations specific for Check boxes/radio buttons.
      if ($select_threshold == DynamicOptions::FORCE_BUTTONS || count($options) <= $select_threshold) {
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

      if ($this->getSetting('threshold_enabled') == 0) {
        $select_threshold = DynamicOptions::FORCE_BUTTONS;
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
