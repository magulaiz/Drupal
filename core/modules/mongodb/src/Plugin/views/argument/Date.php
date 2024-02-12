<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\Date as CoreDate;

/**
 * Overriding the views argument plugin "date".
 */
class Date extends CoreDate {

  use FormulaTrait;

  /**
   * The MongoDB condition operator.
   *
   * @var string
   */
  protected $mongodbOperator = 'DATEDATE';

}
