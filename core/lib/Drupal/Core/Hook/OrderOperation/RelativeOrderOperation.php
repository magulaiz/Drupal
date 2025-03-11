<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

class RelativeOrderOperation implements OrderOperationInterface {

  public function __construct(
    protected readonly string $identifier,
    protected readonly array $modulesToOrderAgainst,
    protected readonly array $identifiersToOrderAgainst,
    protected readonly bool $isAfter,
  ) {}

  public static function before(string $identifier, array $modules_to_order_against, array $identifiers_to_order_against): static {
    return new static($identifier, $modules_to_order_against, $identifiers_to_order_against, FALSE);
  }

  /**
   * @param string $identifier
   * @param list<string> $modules_to_order_against
   * @param list<string> $identifiers_to_order_against
   *   List of implementation identifiers, as "$class::$method".
   *
   * @return static
   *   New instance.
   */
  public static function after(string $identifier, array $modules_to_order_against, array $identifiers_to_order_against): static {
    return new static($identifier, $modules_to_order_against, $identifiers_to_order_against, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function apply(array &$identifiers, array $module_finder): void {
    assert(array_is_list($identifiers));
    $orig = $identifiers;
    $index = array_search($this->identifier, $identifiers);
    if ($index === FALSE) {
      // Nothing to reorder.
      return;
    }
    $identifiers_to_order_against = $this->identifiersToOrderAgainst;
    if ($this->modulesToOrderAgainst) {
      $identifiers_to_order_against = [
        ...$identifiers_to_order_against,
        ...array_keys(array_intersect($module_finder, $this->modulesToOrderAgainst)),
      ];
    }
    $indices_to_order_against = array_keys(array_intersect($identifiers, $identifiers_to_order_against));
    if ($indices_to_order_against === []) {
      return;
    }
    if ($this->isAfter) {
      $max_index_to_order_against = max($indices_to_order_against);
      if ($index >= $max_index_to_order_against) {
        // The element is already after the other elements.
        return;
      }
      array_splice($identifiers, $max_index_to_order_against + 1, 0, $this->identifier);
      // Remove the element after splicing.
      unset($identifiers[$index]);
      return;
    }
    else {
      $min_index_to_order_against = min($indices_to_order_against);
      if ($index <= $min_index_to_order_against) {
        // The element is already before the other elements.
        return;
      }
      // Remove the element before splicing.
      unset($identifiers[$index]);
      array_splice($identifiers, $min_index_to_order_against, 0, $this->identifier);
    }
  }

}
