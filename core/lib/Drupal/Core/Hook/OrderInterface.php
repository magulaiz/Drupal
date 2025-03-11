<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\OrderOperationInterface;

interface OrderInterface {

  /**
   * @param string $identifier
   *   Identifier of the implementation to move to a new position.
   *   The format is "$class::$module".
   *
   * @return \Drupal\Core\Hook\OrderOperation\OrderOperationInterface
   *   Order operation to apply to a hook implementation list.
   */
  public function getOperation(string $identifier): OrderOperationInterface;

}
