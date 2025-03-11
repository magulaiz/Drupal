<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Identifier;

/**
 * @todo fill in.
 */
abstract class IdentifierBase implements \Stringable {

  public function __construct(
    public readonly string $identifier,
    public readonly string $canonicalName,
    public readonly string $machineName,
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
  public function forMachine(): string {
    return $this->machineName;
  }

  /**
   * @todo fill in.
   */
  public function __toString(): string {
    return $this->forMachine();
  }

}
