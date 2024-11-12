<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Query;

interface StrictSqlParamsConditionInterface {

  /**
   * Public getter for the strict parameters flag.
   *
   * @return bool
   *   Whether the resulting query should strictly-bind parameters.
   */
  public function usesStrictParameters(): bool;

}
