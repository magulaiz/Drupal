<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
abstract class IdentifierBase implements \Stringable {

  public function __construct(
    protected readonly IdentifierProcessorBase $identifierProcessor,
    public readonly string $identifier,
    public readonly string $canonicalName,
  ) {
  }

  /**
   * @todo fill in.
   */
  public function canonical(): string {
    return $this->canonicalName;
  }

  /**
   * @todo fill in.
   */
  public function machineName(bool $quoted = TRUE): string {
    return $quoted ? $this->identifierProcessor->quote($this->canonicalName) : $this->canonicalName;
  }

  /**
   * @todo fill in.
   */
  public function __toString(): string {
    return $this->machineName();
  }

}
