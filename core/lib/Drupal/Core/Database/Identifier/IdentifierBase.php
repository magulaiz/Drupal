<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
abstract class IdentifierBase implements \Stringable {

  /**
   * @todo fill in.
   */
  public readonly string $canonicalName;

  public function __construct(
    protected readonly IdentifierProcessorBase $identifierProcessor,
    public readonly string $identifier,
  ) {
  }

  /**
   * @todo fill in.
   */
  abstract public function machineName(bool $quoted = TRUE): string;

  /**
   * @todo fill in.
   */
  public function __toString(): string {
    return $this->machineName();
  }

}
