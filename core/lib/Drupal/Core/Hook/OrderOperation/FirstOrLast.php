<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Moves one listener to the start or end of the list.
 *
 * @internal
 */
class FirstOrLast implements OrderOperationInterface {

  /**
   * Constructor.
   *
   * @param string $identifier
   *   Identifier of the hook listener to move to a new position.
   *   The format is "$class::$method".
   * @param bool $isLast
   *   TRUE to move to the end, FALSE to move to the start.
   */
  public function __construct(
    protected readonly string $identifier,
    protected readonly bool $isLast,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function pack(): array {
    return get_object_vars($this);
  }

  /**
   * {@inheritdoc}
   */
  public function apply(array &$identifiers, array $module_finder): void {
    $index = array_search($this->identifier, $identifiers);
    if ($index === FALSE) {
      // The element does not exist.
      return;
    }
    unset($identifiers[$index]);
    if ($this->isLast) {
      $identifiers[] = $this->identifier;
    }
    else {
      $identifiers = [$this->identifier, ...$identifiers];
    }
    $identifiers = array_values($identifiers);
  }

}
