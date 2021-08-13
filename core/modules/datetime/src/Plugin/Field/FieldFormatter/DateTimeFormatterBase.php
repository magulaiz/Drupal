<?php

namespace Drupal\datetime\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\datetime\Plugin\Field\ConfigurableTimezoneTrait;
use Drupal\datetime\Plugin\Field\ConfigurableTimezoneInterface;

/**
 * Base class for 'DateTime Field formatter' plugin implementations.
 */
abstract class DateTimeFormatterBase extends FormatterBase implements ConfigurableTimezoneInterface {

  use ConfigurableTimezoneTrait;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The date format entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dateFormatStorage;

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
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $date_format_storage
   *   The date format entity storage.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, DateFormatterInterface $date_formatter, EntityStorageInterface $date_format_storage, ConfigFactoryInterface $config_factory = NULL) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->dateFormatter = $date_formatter;
    $this->dateFormatStorage = $date_format_storage;
    if (!$config_factory) {
      @trigger_error('The config.factory service must be passed to DateTimeFormatterBase::__construct(), it is required before Drupal 9.0.0. See https://www.drupal.org/node/2632040.', E_USER_DEPRECATED);
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
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date.formatter'),
      $container->get('entity_type.manager')->getStorage('date_format'),
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
    $form['timezone_per_date']['#description'] = $this->t('Where a time zone has been specified for a particular date value, use that instead of the default selected above.');

    $form['timezone_override'] = [
      '#type' => 'select',
      '#title' => $this->t('Time zone override'),
      '#description' => $this->t('The time zone selected here will always be used'),
      '#options' => system_time_zones(TRUE, TRUE),
      '#default_value' => $this->getSetting('timezone_override'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary = [];
    $default_timezone_text = $this->getDefaultTimezoneText();
    if ($this->getSetting('timezone_per_date')) {
      $summary[] = $this->t('Use time zones from individual dates');
      $summary[] = $this->t('Use @timezone if no individual time zone specified', ['@timezone' => $default_timezone_text]);
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->date) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;
        $elements[$delta] = $this->buildDateWithIsoAttribute($date, $item->timezone);

        if (!empty($item->_attributes)) {
          $elements[$delta]['#attributes'] += $item->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and should not be rendered in the field template.
          unset($item->_attributes);
        }
      }
    }

    return $elements;
  }

  /**
   * Creates a formatted date value as a string.
   *
   * @param object $date
   *   A date object.
   *
   * @return string
   *   A formatted date string using the chosen format.
   */
  abstract protected function formatDate($date);

  /**
   * Sets the proper time zone on a DrupalDateTime object.
   *
   * A DrupalDateTime object loaded from the database will have the UTC time
   * zone applied to it. This method applies the proper timezone based on
   * the formatter configuration.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A DrupalDateTime object.
   * @param string $date_instance_timezone
   *   (Optional) Time zone associated with the specific date field instance.
   */
  protected function setTimeZone(DrupalDateTime $date, $date_instance_timezone = NULL) {
    $timezone = $this->getDefaultTimezone($date_instance_timezone);
    $date->setTimeZone(timezone_open($timezone));
  }

  /**
   * Gets a settings array suitable for DrupalDateTime::format().
   *
   * @return array
   *   The settings array that can be passed to DrupalDateTime::format().
   */
  protected function getFormatSettings() {
    $settings = [];

    if (!$this->getSetting('timezone_per_date') && $this->getSetting('timezone_override') != '') {
      $settings['timezone'] = $this->getSetting('timezone_override');
    }

    return $settings;
  }

  /**
   * Creates a render array from a date object.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A date object.
   * @param string $timezone
   *   (Optional) A time zone to explicitly set the date to.
   *
   * @return array
   *   A render array.
   */
  protected function buildDate(DrupalDateTime $date, $timezone = NULL) {
    $this->setTimeZone($date, $timezone);

    $build = [
      '#markup' => $this->formatDate($date),
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Creates a render array from a date object with ISO date attribute.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   A date object.
   * @param string $timezone
   *   (Optional) A time zone to explicitly set the date to.
   *
   * @return array
   *   A render array.
   */
  protected function buildDateWithIsoAttribute(DrupalDateTime $date, $timezone = NULL) {
    // Create the ISO date in Universal Time.
    $iso_date = $date->format("Y-m-d\TH:i:s") . 'Z';

    $this->setTimeZone($date, $timezone);

    $build = [
      '#theme' => 'time',
      '#text' => $this->formatDate($date),
      '#attributes' => [
        'datetime' => $iso_date,
      ],
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
    ];

    return $build;
  }

}
