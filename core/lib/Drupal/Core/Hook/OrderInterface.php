<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\OrderOperationInterface;

interface OrderInterface {

  /**
   * @param string $identifier
   *
   * @return \Drupal\Core\Hook\OrderOperation\OrderOperationInterface
   */
  public function getOperation(string $identifier): OrderOperationInterface;

}
