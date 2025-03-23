<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Moves one listener to be called before or after other listeners.
 *
 * @internal
 */
class BeforeOrAfterIdentifier extends OrderOperation {

  /**
   * Constructor.
   *
   * @param string $identifier
   *   Identifier of the hook listener to move to a new position.
   *   The format is "$class::$method".
   * @param string $identifierToOrderAgainst
   *   Identifiers of listeners to order against.
   *   The format is "$class::$method".
   * @param bool $isAfter
   *   TRUE, if the listener to move should be moved after the listener to order
   *   against, FALSE if it should be moved before.
   */
  public function __construct(
    protected readonly string $identifier,
    protected readonly string $identifierToOrderAgainst,
    protected readonly bool $isAfter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function apply(array &$identifiers, array $module_finder): void {
    assert(array_is_list($identifiers));
    $index = array_search($this->identifier, $identifiers);
    if ($index === FALSE) {
      // Nothing to reorder.
      return;
    }
    $index_to_order_against = array_search($this->identifierToOrderAgainst, $identifiers);
    if ($index_to_order_against === FALSE) {
      return;
    }
    if ($this->isAfter) {
      if ($index >= $index_to_order_against) {
        // The element is already after the other element.
        return;
      }
      array_splice($identifiers, $index_to_order_against + 1, 0, $this->identifier);
      // Remove the element after splicing.
      unset($identifiers[$index]);
      $identifiers = array_values($identifiers);
    }
    else {
      if ($index <= $index_to_order_against) {
        // The element is already before the other elements.
        return;
      }
      // Remove the element before splicing.
      unset($identifiers[$index]);
      array_splice($identifiers, $index_to_order_against, 0, $this->identifier);
    }
  }

}
