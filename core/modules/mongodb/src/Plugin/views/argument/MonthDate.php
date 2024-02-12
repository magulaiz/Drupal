<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\MonthDate as CoreMonthDate;

/**
 * Overriding the views argument plugin "date_month".
 */
class MonthDate extends CoreMonthDate {

  use FormulaTrait;

  /**
   * The MongoDB condition operator.
   *
   * @var string
   */
  protected $mongodbOperator = 'DATEDATE';

}
