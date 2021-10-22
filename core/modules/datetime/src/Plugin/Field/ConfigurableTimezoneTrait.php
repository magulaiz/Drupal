<?php

namespace Drupal\datetime\Plugin\Field;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Time zone settings common to datetime widgets and formatters.
 */
trait ConfigurableTimezoneTrait {

  /**
   * Add time zone settings to a widget or formatter settings form.
   *
   * @return array
   *   The form structure with time zone settings elements added.
   */
  protected function timezoneSettingsForm() {
    $form = [];

    // Timezone display is only applicable to datetime items.
    $datetime_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('datetime_type');
    if ($datetime_type === DateTimeItem::DATETIME_TYPE_DATETIME) {
      $form['timezone_default'] = [
        '#type' => 'select',
        '#title' => $this->t('Default time zone'),
        '#description' => $this->t('The time zone to use by default when displaying this date.'),
        '#options' => [
          ConfigurableTimezoneInterface::TIMEZONE_USER => $this->t("The user's account time zone"),
          ConfigurableTimezoneInterface::TIMEZONE_SITE => $this->t("The site's default time zone"),
          ConfigurableTimezoneInterface::TIMEZONE_FIXED => $this->t('A fixed time zone'),
        ],
        '#default_value' => $this->getSetting('timezone_default'),
      ];

      $form['timezone_override'] = [
        '#type' => 'select',
        '#title' => $this->t('Fixed time zone'),
        '#options' => system_time_zones(TRUE),
        '#default_value' => $this->getSetting('timezone_override'),
        '#states' => ['visible' => [':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][timezone_default]"]' => ['value' => ConfigurableTimezoneInterface::TIMEZONE_FIXED]]],
      ];

      // If this field is using per-date time zone storage, give the option of
      // allowing that to override the default.
      $timezone_storage = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('timezone_storage');
      if ($timezone_storage) {
        $form['timezone_per_date'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Preferred time zone for each date'),
          '#default_value' => $this->getSetting('timezone_per_date'),
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultTimezone($item_timezone = NULL) {
    if ($this->getFieldSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      // A date without time has no time zone conversion.
      $timezone = DateTimeItemInterface::STORAGE_TIMEZONE;
    }
    else {
      $timezone_default = $this->getSetting('timezone_default');
      $timezone_override = $this->getSetting('timezone_override');
      if ($this->getSetting('timezone_per_date') && !empty($item_timezone)) {
        $timezone = $item_timezone;
      }
      elseif ($timezone_override && $timezone_default === ConfigurableTimezoneInterface::TIMEZONE_FIXED) {
        $timezone = $timezone_override;
      }
      elseif ($timezone_default === ConfigurableTimezoneInterface::TIMEZONE_SITE) {
        $timezone = $this->config->get('system.date')->get('timezone.default');
      }
      else {
        $timezone = date_default_timezone_get();
      }
    }
    return $timezone;
  }

  /**
   * Text explaining the default time zone.
   *
   * @return string
   *   A string describing the default time zone.
   */
  protected function getDefaultTimezoneText() {
    $summary = [];
    $datetime_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('datetime_type');
    if ($datetime_type === DateTimeItem::DATETIME_TYPE_DATETIME) {
      // Determine the default time zone summary text.
      $timezone_default = $this->getSetting('timezone_default');
      $timezone_override = $this->getSetting('timezone_override');
      if ($timezone_override && $timezone_default === ConfigurableTimezoneInterface::TIMEZONE_FIXED) {
        return $timezone_override;
      }
      elseif ($timezone_default === ConfigurableTimezoneInterface::TIMEZONE_SITE) {
        return $this->t("the site's default time zone");
      }
      else {
        return $this->t("the user's account time zone");
      }
    }

    return '';
  }

}
