<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\FirstOrLast;

/**
 * Set this implementation to be first or last.
 */
enum Order: int implements OrderInterface {

  // This implementation should fire first.
  case First = 1;

  // This implementation should fire last.
  case Last = 0;

  /**
   * {@inheritdoc}
   */
  public function getOperations(string $identifier): array {
    return [new FirstOrLast($identifier, $this === self::Last)];
  }

}
