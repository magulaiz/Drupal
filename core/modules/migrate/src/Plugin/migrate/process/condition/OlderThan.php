<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;

/**
 * Provides an older_than condition.
 *
 * The source must be a datetime string in the configured format. The date
 * we compare to (either value or property) can be either a datetime string
 * understood by strtotime() or a datetime string in the configured format.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) A date string as accepted
 *   by strtotime() or a datetime string matching the configured format,
 *   against which the source value should be compared.
 * - property: (one of value or property is required) The source or destination
 *   property containing a date string as accepted by strtotime() against which
 *   the source value should be compared.
 * - format: The format of the source as accepted by
 *   DateTime::createFromFormat().
 *
 * Examples:
 *
 * Skip the row if created_on holds a date more than a month old.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     method: row
 *     condition: older_than
 *     configuration:
 *       format: 'j M Y'
 *       value: '-1 month'
 *     source: created_on
 * @endcode
 *
 * Skip the row if field_timestamp is newer than 1642973915.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     method: row
 *     negate: true
 *     condition: older_than
 *     configuration:
 *       format: 'U'
 *       value: '1642973915'
 *     source: field_timestamp
 * @endcode
 *
 * Skip the row if field_updated is before field_created. Because
 * how was something updated before it was created?
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     method: row
 *     condition: older_than
 *     configuration:
 *       format: 'Y-m-d H:i:s'
 *       property: field_updated
 *     source: field_created
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "older_than",
 *   requires = {"format"}
 * )
 */
class OlderThan extends ProcessConditionPluginBase {

  /**
   * The static date used by all rows.
   *
   * @var Drupal\Component\Datetime\DateTimePlus
   */
  protected $valueDate;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Exactly one of value and property and must be set.
    if (array_key_exists('value', $configuration) && isset($configuration['property'])) {
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id process condition.");
    }
    if (isset($configuration['property'])) {
      if (!is_string($configuration['property'])) {
        throw new \InvalidArgumentException("The property configuration must be a string when using the $plugin_id process condition.");
      }
    }
    elseif (array_key_exists('value', $configuration)) {
      try {
        $this->valueDate = DateTimePlus::createFromTimestamp(strtotime($configuration['value']));
      }
      catch (\InvalidArgumentException $e) {
        try {
          $this->valueDate = DateTimePlus::createFromFormat($configuration['format'], $configuration['value']);
        }
        catch (\InvalidArgumentException $e) {
          throw new \InvalidArgumentException("The 'value' passed to older_than could not be converted into a datetime object.");
        }
      }
    }
    else {
      // This means that neither value nor property is set.
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id process condition.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if ($this->valueDate) {
      $value_date = $this->valueDate;
    }
    else {
      try {
        $value_date = DateTimePlus::createFromTimestamp(strtotime($row->get($this->configuration['property'])));
      }
      catch (\InvalidArgumentException $e) {
        try {
          $value_date = DateTimePlus::createFromFormat($this->configuration['format'], $row->get($this->configuration['property']));
        }
        catch (\InvalidArgumentException $e) {
          throw new MigrateException("The 'property' passed to older_than could not be converted into a datetime object.");
        }
      }
    }

    try {
      $source_date = DateTimePlus::createFromFormat($this->configuration['format'], $source);
    }
    catch (\InvalidArgumentException $e) {
      throw new MigrateException($e->getMessage());
    }

    return (bool) $value_date->diff($source_date)->invert;
  }

}
