<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * @internal
 */
interface OrderOperationInterface {

  /**
   * Changes the order of a list of hook implementations.
   *
   * @param list<string> $identifiers
   *   Implementation identifiers, as "$class::$method".
   * @param array<string, string> $module_finder
   *   Lookup map to find a module name for each implementation.
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
