<?php

namespace Drupal\datetime\Plugin\views\argument;

use Drupal\datetime\Plugin\views\argument\Date;

/**
 * Overriding the views argument plugin "datetime".
 */
class DatetimeDate extends Date {

  use DatetimeDateTrait;

}
