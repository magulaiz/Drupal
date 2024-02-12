<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\WeekDate as CoreWeekDate;

/**
 * Overriding the views argument plugin "date_week".
 */
class WeekDate extends CoreWeekDate {

  use FormulaTrait;

  /**
   * The MongoDB condition operator.
   *
   * @var string
   */
  protected $mongodbOperator = 'DATEDATE';

}
