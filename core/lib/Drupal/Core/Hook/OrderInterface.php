<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\OrderOperationInterface;

interface OrderInterface {

  /**
   * @param string $identifier
   *   Identifier.
   *
   * @return \Drupal\Core\Hook\OrderOperation\OrderOperationInterface
   *   Order operation.
   */
  public function getOperation(string $identifier): OrderOperationInterface;

}
