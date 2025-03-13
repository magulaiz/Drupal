<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

class AbsoluteOrderOperation implements OrderOperationInterface {

  public function __construct(
    protected readonly string $identifier,
    protected readonly bool $isLast,
  ) {}

  public static function first(string $identifier): static {
    return new static($identifier, FALSE);
  }

  public static function last(string $identifier): static {
    return new static($identifier, TRUE);
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
  }

}
