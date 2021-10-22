<?php

namespace Drupal\datetime\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\ConfigurableTimezoneInterface;
use Drupal\datetime\Plugin\Field\ConfigurableTimezoneTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for the 'datetime_*' widgets.
 */
class DateTimeWidgetBase extends WidgetBase implements ConfigurableTimezoneInterface, ContainerFactoryPluginInterface {

  use ConfigurableTimezoneTrait;

  /**
   * A config factory for retrieving required config settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a new DateTimeDefaultFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConfigFactoryInterface $config_factory = NULL) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    if (!$config_factory) {
      @trigger_error('The config.factory service must be passed to DateTimeWidgetBase::__construct(), it is required before Drupal 9.0.0. See https://www.drupal.org/node/2632040.', E_USER_DEPRECATED);
      $config_factory = \Drupal::service('config.factory');
    }
    $this->config = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'timezone_default' => ConfigurableTimezoneInterface::TIMEZONE_USER,
      'timezone_override' => '',
      'timezone_per_date' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = NestedArray::mergeDeep($this->timezoneSettingsForm(), parent::settingsForm($form, $form_state));
    $form['timezone_per_date']['#description'] = "Allow users to specify a time zone when entering a date, and store this as the preferred time zone for that date.";
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $default_timezone_text = $this->getDefaultTimezoneText();
    if ($this->getSetting('timezone_per_date')) {
      $summary[] = $this->t('Allow users to choose a time zone');
      $summary[] = $this->t('Default to @timezone', ['@timezone' => $default_timezone_text]);
    }
    else {
      $summary[] = $this->t('Time zone: @timezone', ['@timezone' => $default_timezone_text]);
    }
    $summary = NestedArray::mergeDeep($summary, parent::settingsSummary());
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = [
      '#type' => 'datetime',
      '#default_value' => NULL,
      '#date_increment' => 1,
      '#date_timezone' => $this->getDefaultTimezone($items[$delta]->timezone),
      '#required' => $element['#required'],
    ];

    if ($this->getSetting('timezone_per_date') && $this->getFieldSetting('timezone_storage') === TRUE) {
      $element['value']['#expose_timezone'] = TRUE;
    }

    if ($items[$delta]->date) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
      $date = $items[$delta]->date;
      // The date was created and verified during field_load(), so it is safe to
      // use without further inspection.
      // @todo Remove after #2799987, as then the element will handle this.
      $date->setTimezone(new \DateTimeZone($element['value']['#date_timezone']));
      $element['value']['#default_value'] = $this->createDefaultValue($date, $element['value']['#date_timezone']);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // The widget form element type has transformed the date value to a
    // DrupalDateTime object at this point. We need to extract the time zone
    // and store it separately, and then convert the date to Drupal's storage
    // time zone and format.

    $datetime_type = $this->getFieldSetting('datetime_type');
    if ($datetime_type === DateTimeItem::DATETIME_TYPE_DATE) {
      $storage_format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    }
    else {
      $storage_format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    }

    $storage_timezone = new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE);

    foreach ($values as &$item) {
      if (!empty($item['value']) && $item['value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item['value'];

        // Store the time zone if appropriate.
        $item['timezone'] = '';
        if ($this->shouldStoreTimezone($date, $form, $form_state) && $this->getFieldSetting('timezone_storage') === TRUE) {
          $item['timezone'] = $date->getTimezone()->getName();
        }

        // Adjust the date for storage once validation is complete.
        if ($form_state->isValidationComplete()) {
          $date->setTimezone($storage_timezone);
        }

        // Adjust the date for storage.
        $item['value'] = $date->format($storage_format);
      }
    }
    return $values;
  }

  /**
   * Determines whether the time zone should be stored.
   *
   * @return bool
   *   Whether the time zone should be stored.
   */
  protected function shouldStoreTimezone() {
    return $this->fieldDefinition->getFieldStorageDefinition()->getSetting('timezone_storage');
  }

  /**
   * Creates a date object for use as a default value.
   *
   * This will take a default value, apply the proper timezone for display in
   * a widget, and set the default time for date-only fields.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   The UTC default date.
   * @param string $timezone
   *   The timezone to apply.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   A date object for use as a default value in a field widget.
   */
  protected function createDefaultValue($date, $timezone) {
    // The date was created and verified during field_load(), so it is safe to
    // use without further inspection.
    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $date->setDefaultDateTime();
    }
    // @todo Remove after #2799987, as then the element will handle this.
    $date->setTimezone(new \DateTimeZone($timezone));
    return $date;
  }

}
