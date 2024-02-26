<?php

namespace Drupal\datetime_range\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\datetime\DateTimeComputed;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Plugin implementation of the 'daterange' field type.
 *
 * @FieldType(
 *   id = "daterange",
 *   label = @Translation("Date range"),
 *   category = "date_time",
 *   description = {
 *     @Translation("Ideal for storing durations that consist of start and end dates (and times)"),
 *     @Translation("Choose between setting both date and time, or date only, for each duration"),
 *     @Translation("The system automatically validates that the end date (and time) is later than the start, and both fields are completed"),
 *   },
 *   default_widget = "daterange_default",
 *   default_formatter = "daterange_default",
 *   list_class = "\Drupal\datetime_range\Plugin\Field\FieldType\DateRangeFieldItemList"
 * )
 */
class DateRangeItem extends DateTimeItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'optional_values' => static::OPTIONAL_NONE,
    ] + parent::defaultFieldSettings();
  }

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_ALLDAY = 'allday';

  /**
   * Optional values setting, require a bounded datetime range.
   */
  const OPTIONAL_NONE = 0x01;

  /**
   * Optional values setting, do not require an end date.
   */
  const OPTIONAL_END = 0x02;

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('Start date value'))
      ->setRequired(TRUE);

    $properties['start_date'] = DataDefinition::create('any')
      ->setLabel(t('Computed start date'))
      ->setDescription(t('The computed start DateTime object.'))
      ->setComputed(TRUE)
      ->setClass(DateTimeComputed::class)
      ->setSetting('date source', 'value');

    $properties['end_value'] = DataDefinition::create('datetime_iso8601')
      ->setLabel(t('End date value'))
      ->setRequired(FALSE);

    $properties['end_date'] = DataDefinition::create('any')
      ->setLabel(t('Computed end date'))
      ->setDescription(t('The computed end DateTime object.'))
      ->setComputed(TRUE)
      ->setClass(DateTimeComputed::class)
      ->setSetting('date source', 'end_value');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['value']['description'] = 'The start date value.';

    $schema['columns']['end_value'] = [
      'description' => 'The end date value.',
    ] + $schema['columns']['value'];

    $schema['indexes']['end_value'] = ['end_value'];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['optional_values'] = [
      '#type' => 'radios',
      '#title' => $this->t('End date'),
      '#description' => $this->t('Whether an end date must be provided if a start date is given.'),
      '#default_value' => $this->getSetting('optional_values'),
      '#options' => [
        static::OPTIONAL_NONE => $this->t('Required'),
        static::OPTIONAL_END => $this->t('Optional'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $type = $field_definition->getSetting('datetime_type');

    // Just pick a date in the past year. No guidance is provided by this Field
    // type.
    $start = \Drupal::time()->getRequestTime() - mt_rand(0, 86400 * 365) - 86400;
    $end = $start + 86400;
    if ($type == static::DATETIME_TYPE_DATETIME) {
      $values['value'] = gmdate(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $start);
      $values['end_value'] = gmdate(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, $end);
    }
    else {
      $values['value'] = gmdate(DateTimeItemInterface::DATE_STORAGE_FORMAT, $start);
      $values['end_value'] = gmdate(DateTimeItemInterface::DATE_STORAGE_FORMAT, $end);
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $start_value = $this->get('value')->getValue();
    $end_value = $this->get('end_value')->getValue();
    return ($start_value === NULL || $start_value === '') && ($end_value === NULL || $end_value === '');
  }

  /**
   * {@inheritdoc}
   */
  public function onChange($property_name, $notify = TRUE) {
    // Enforce that the computed date is recalculated.
    if ($property_name == 'value') {
      $this->start_date = NULL;
    }
    elseif ($property_name == 'end_value') {
      $this->end_date = NULL;
    }
    parent::onChange($property_name, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $constraint_manager = \Drupal::typedDataManager()
      ->getValidationConstraintManager();
    if ($this->getSetting('optional_values') == static::OPTIONAL_NONE) {
      $label = $this->getFieldDefinition()->getLabel();
      $constraints[] = $constraint_manager
        ->create('ComplexData', [
          'end_value' => [
            'NotNull' => [
              'message' => $this->t('The @title end date is required', ['@title' => $label]),
            ],
          ],
        ]);
    }

    return $constraints;
  }

}
