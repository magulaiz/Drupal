<?php

namespace Drupal\options\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Url;

/**
 * Plugin base class inherited by the options field types.
 */
abstract class ListItemBase extends FieldItemBase implements OptionsProviderInterface {

  use MessengerTrait;

  /**
   * The predefined options plugin manager service.
   *
   * @var \Drupal\options\Plugin\PredefinedOptionsPluginManager
   */
  protected $predefinedOptionsManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(DataDefinitionInterface $definition, $name = NULL, TypedDataInterface $parent = NULL) {
    parent::__construct($definition, $name, $parent);
    $this->predefinedOptionsManager = \Drupal::service('plugin.manager.options.predefined_options');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'allowed_values' => [],
      'predefined_options_plugin' => '',
      'allowed_values_function' => '',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleValues(AccountInterface $account = NULL) {
    // Flatten options firstly, because Possible Options may contain group
    // arrays.
    $flatten_options = OptGroup::flattenOptions($this->getPossibleOptions($account));
    return array_keys($flatten_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(AccountInterface $account = NULL) {
    return $this->getSettableOptions($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableValues(AccountInterface $account = NULL) {
    // Flatten options firstly, because Settable Options may contain group
    // arrays.
    $flatten_options = OptGroup::flattenOptions($this->getSettableOptions($account));
    return array_keys($flatten_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableOptions(AccountInterface $account = NULL) {
    $allowed_options = options_allowed_values($this->getFieldDefinition()->getFieldStorageDefinition(), $this->getEntity());
    return $allowed_options;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $allowed_options = options_allowed_values($field_definition->getFieldStorageDefinition());
    if (empty($allowed_options)) {
      $values['value'] = NULL;
      return $values;
    }
    $values['value'] = array_rand($allowed_options);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->value) && (string) $this->value !== '0';
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $allowed_values = $this->getSetting('allowed_values');
    $predefined_options_plugin = $this->getSetting('predefined_options_plugin');
    $allowed_values_function = $this->getSetting('allowed_values_function');
    $options = ['' => $this->t('Custom')] + $this->predefinedOptionsManager->getAvailablePlugins();

    $element['predefined_options_plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Allowed values'),
      '#options' => $options,
      '#default_value' => !empty($predefined_options_plugin) ? $predefined_options_plugin : NULL,
      '#weight' => -20,
    ];

    $element['allowed_values'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed values list'),
      '#default_value' => $this->allowedValuesString($allowed_values),
      '#rows' => 10,
      '#element_validate' => [[static::class, 'validateAllowedValues']],
      '#field_has_data' => $has_data,
      '#field_name' => $this->getFieldDefinition()->getName(),
      '#entity_type' => $this->getEntity()->getEntityTypeId(),
      '#allowed_values' => $allowed_values,
      '#required' => TRUE,
      '#access' => empty($allowed_values_function),
    ];

    $element['allowed_values']['#description'] = $this->allowedValuesDescription();
    $element['allowed_values']['#states'] = [
      'invisible' => [
        '[name*="predefined_options_plugin"]' => ['!value' => ''],
      ],
    ];

    $element['allowed_values_function'] = [
      '#type' => 'item',
      '#title' => $this->t('Allowed values list'),
      '#markup' => $this->t('The value of this field is being determined by the %function function and may not be changed.', ['%function' => $allowed_values_function]),
      '#access' => !empty($allowed_values_function),
      '#value' => $allowed_values_function,
    ];

    $this->throwDeprecationMessage($allowed_values_function);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $plugin_id = $this->getSetting('predefined_options_plugin');

    if (!empty($plugin_id)) {
      try {
        $plugin = $this->predefinedOptionsManager->getDefinition($plugin_id);
        $element['predefined_options_plugin'] = [
          '#markup' => $this->t('<b>Predefined options plugin</b>: %plugin', [
            '%plugin' => $plugin['label'],
          ]),
        ];
      }
      catch (\Exception $e) {
        watchdog_exception('options', $e);
        $this->messenger()->addError($e->getMessage());
      }
    }
    elseif (!empty($allowed_values_function = $this->getSetting('allowed_values_function'))) {
      $this->throwDeprecationMessage($allowed_values_function);
    }

    return $element;
  }

  /**
   * Provides the field type specific allowed values form element #description.
   *
   * @return string
   *   The field type allowed values form specific description.
   */
  abstract protected function allowedValuesDescription();

  /**
   * The #element_validate callback for options field allowed values.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateAllowedValues(array $element, FormStateInterface $form_state) {
    $values = static::extractAllowedValues($element['#value'], $element['#field_has_data']);

    if (!is_array($values)) {
      $form_state->setError($element, new TranslatableMarkup('Allowed values list: invalid input.'));
    }
    else {
      // Check that keys are valid for the field type.
      foreach ($values as $key => $value) {
        if ($error = static::validateAllowedValue($key)) {
          $form_state->setError($element, $error);
          break;
        }
      }

      // Prevent removing values currently in use.
      if ($element['#field_has_data']) {
        $lost_keys = array_keys(array_diff_key($element['#allowed_values'], $values));
        if (_options_values_in_use($element['#entity_type'], $element['#field_name'], $lost_keys)) {
          $form_state->setError($element, new TranslatableMarkup('Allowed values list: some values are being removed while currently in use.'));
        }
      }

      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   * @param bool $has_data
   *   The current field already has data inserted or not.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string, $has_data) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can use the value as the key.
      elseif (!static::validateAllowedValue($text)) {
        $key = $value = $text;
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can generate a key from the position.
      elseif (!$has_data) {
        $key = (string) $position;
        $value = $text;
        $generated_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicit_keys && $generated_keys) {
      return;
    }

    return $values;
  }

  /**
   * Checks whether a candidate allowed value is valid.
   *
   * @param string $option
   *   The option value entered by the user.
   *
   * @return string
   *   The error message if the specified value is invalid, NULL otherwise.
   */
  protected static function validateAllowedValue($option) {}

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected function allowedValuesString(array $values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * {@inheritdoc}
   */
  public static function storageSettingsToConfigData(array $settings) {
    if (isset($settings['allowed_values'])) {
      $settings['allowed_values'] = static::structureAllowedValues($settings['allowed_values']);
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function storageSettingsFromConfigData(array $settings) {
    if (isset($settings['allowed_values'])) {
      $settings['allowed_values'] = static::simplifyAllowedValues($settings['allowed_values']);
    }
    return $settings;
  }

  /**
   * Simplifies allowed values to a key-value array from the structured array.
   *
   * @param array $structured_values
   *   Array of items with a 'value' and 'label' key each for the allowed
   *   values.
   *
   * @return array
   *   Allowed values were the array key is the 'value' value, the value is
   *   the 'label' value.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::structureAllowedValues()
   */
  protected static function simplifyAllowedValues(array $structured_values) {
    $values = [];
    foreach ($structured_values as $item) {
      if (is_array($item['label'])) {
        // Nested elements are embedded in the label.
        $item['label'] = static::simplifyAllowedValues($item['label']);
      }
      $values[$item['value']] = $item['label'];
    }
    return $values;
  }

  /**
   * Creates a structured array of allowed values from a key-value array.
   *
   * @param array $values
   *   Allowed values were the array key is the 'value' value, the value is
   *   the 'label' value.
   *
   * @return array
   *   Array of items with a 'value' and 'label' key each for the allowed
   *   values.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::simplifyAllowedValues()
   */
  protected static function structureAllowedValues(array $values) {
    $structured_values = [];
    foreach ($values as $value => $label) {
      if (is_array($label)) {
        $label = static::structureAllowedValues($label);
      }
      $structured_values[] = [
        'value' => static::castAllowedValue($value),
        'label' => $label,
      ];
    }
    return $structured_values;
  }

  /**
   * Converts a value to the correct type.
   *
   * @param mixed $value
   *   The value to cast.
   *
   * @return mixed
   *   The casted value.
   */
  protected static function castAllowedValue($value) {
    return $value;
  }

  /**
   * Throw an error message warning about callback deprecation.
   *
   * This is a temporary method to warn developers using callback
   * 'allowed_values_function'. This can be safely removed on any minor
   * release after the predefined options plugin feature is added to core.
   *
   * @param string $allowed_values_function
   *   The 'allowed_values_function' name.
   */
  private function throwDeprecationMessage($allowed_values_function) {
    if (!empty($allowed_values_function)) {
      $this->messenger()
        ->addWarning($this->t('The use of callback functions is deprecated. Please replace %function function with a plugin. :link.', [
          '%function' => $allowed_values_function,
          ':link' => Link::fromTextAndUrl('Check the docs.', Url::fromUri('https://www.drupal.org/docs/8/core/modules/options')),
        ]), TRUE);
    }
  }

}
