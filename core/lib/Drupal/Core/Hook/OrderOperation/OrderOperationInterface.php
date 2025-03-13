<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

interface OrderOperationInterface {

  /**
   * Alters a list of hook implementations.
   *
   * @param list<string> $identifiers
   *   Implementation identifiers, as "$class::$method".
   * @param array<string, string> $module_finder
   *   Lookup map to find a module name for each implementation.
   *   This may contain more entries than $identifiers.
   */
  public function apply(array &$identifiers, array $module_finder): void;

}
