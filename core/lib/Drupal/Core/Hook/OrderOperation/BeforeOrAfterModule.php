<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Moves one listener to be called before or after other listeners.
 *
 * @internal
 */
class BeforeOrAfterModule extends OrderOperation {

  /**
   * Constructor.
   *
   * @param string $identifier
   *   Identifier of the hook listener to move to a new position.
   *   The format is "$class::$method".
   * @param string $moduleToOrderAgainst
   *   Module names of listeners to order against.
   * @param bool $isAfter
   *   TRUE, if the listener to move should be moved after the listener to order
   *   against, FALSE if it should be moved before.
   */
  public function __construct(
    protected readonly string $identifier,
    protected readonly string $moduleToOrderAgainst,
    protected readonly bool $isAfter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function apply(array &$identifiers, array $module_finder): void {
    assert(array_is_list($identifiers));
    if (!in_array($this->identifier, $identifiers)) {
      // Nothing to reorder.
      return;
    }
    $identifiers_to_order_against = array_keys($module_finder, $this->moduleToOrderAgainst);
    foreach ($identifiers_to_order_against as $identifier_to_order_against) {
      $operation = new BeforeOrAfterIdentifier($this->identifier, $identifier_to_order_against, $this->isAfter);
      $operation->apply($identifiers, $module_finder);
    }
  }

}
