<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\AbsoluteOrderOperation;
use Drupal\Core\Hook\OrderOperation\OrderOperationInterface;

/**
 * Set this implementation to be first or last.
 */
enum Order: int implements OrderInterface {

  // This implementation should fire first.
  case First = 1;

  // This implementation should fire last.
  case Last = 0;

  public function getOperation(string $identifier): OrderOperationInterface {
    return new AbsoluteOrderOperation($identifier, $this === self::Last);
  }

}
