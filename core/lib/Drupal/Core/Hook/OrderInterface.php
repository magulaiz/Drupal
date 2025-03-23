<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook;

/**
 * Interface for order specifiers used in hook attributes.
 */
interface OrderInterface {

  /**
   * Gets order operations specified by this object.
   *
   * @param string $identifier
   *   Identifier of the implementation to move to a new position.
   *   The format is "$class::$module".
   *
   * @return list<\Drupal\Core\Hook\OrderOperation\OrderOperation>
   *   Order operation to apply to a hook implementation list.
   */
  public function getOperations(string $identifier): array;

}
