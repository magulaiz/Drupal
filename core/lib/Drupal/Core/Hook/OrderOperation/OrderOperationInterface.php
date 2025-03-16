<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Operations that changes the order of hook listeners.
 *
 * Note that these are operations, not constraints, and operations applied
 * earlier can be overridden by implementations applied later.
 *
 * @internal
 */
interface OrderOperationInterface {

  /**
   * Changes the order of a list of hook listeners.
   *
   * @param list<string> $identifiers
   *   Hook listener identifiers, as "$class::$method", to be changed by
   *   reference.
   *   The order operation must make sure that the array remains a list, and
   *   that the values are the same as before.
   * @param array<string, string> $module_finder
   *   Lookup map to find a module name for each listener.
   *   This may contain more entries than $identifiers.
   */
  public function apply(array &$identifiers, array $module_finder): void;

  /**
   * Packs the object properties.
   *
   * @return array
   *   An array to pass as arguments to the constructor.
   *   Keys can be parameter names or indices.
   */
  public function pack(): array;

}
