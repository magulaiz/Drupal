<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
abstract class IdentifierBase implements \Stringable {

  public function __construct(
    protected readonly IdentifierHandler $identifierHandler,
    public readonly string $identifier,
  ) {
  }

  /**
   * @todo fill in.
   */
  public function __toString(): string {
    return $this->machineName();
  }

}
