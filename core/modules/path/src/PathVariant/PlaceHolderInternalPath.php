<?php

declare(strict_types=1);

namespace Drupal\path\PathVariant;

/**
 * Represents an internal path that cannot be determined yet.
 *
 * Usually since an entity hasn't been saved yet.
 */
final class PlaceHolderInternalPath {

  private function __construct() {}

  /**
   * Creates a new internal path placeholder.
   *
   * @internal
   *   Not for public use.
   */
  public static function create(): static {
    return new static();
  }

}
