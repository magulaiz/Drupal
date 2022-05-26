<?php

namespace Drupal\datetime_range\Plugin\Field\FieldType;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Interface definition for Daterange items.
 */
interface DateRangeItemInterface extends DateTimeItemInterface {

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_ALLDAY = 'allday';

}