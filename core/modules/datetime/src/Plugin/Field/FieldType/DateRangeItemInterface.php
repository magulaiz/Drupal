<?php

namespace Drupal\datetime\Plugin\Field\FieldType;

/**
 * Interface definition for Daterange items.
 */
interface DateRangeItemInterface extends DateTimeItemInterface {

  /**
   * Value for the 'datetime_type' setting: store a date and time.
   */
  const DATETIME_TYPE_ALLDAY = 'allday';

}
