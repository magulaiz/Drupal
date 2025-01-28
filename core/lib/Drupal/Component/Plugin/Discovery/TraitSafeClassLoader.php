<?php

declare(strict_types=1);

namespace Drupal\Component\Plugin\Discovery;

/**
 * Defines a classloader that throws an exception.
 *
 * This is not really a classloader, but exists to workaround a PHP limitation
 * when it attempts to load a class that relies on a trait that does not exist.
 * This is a common situation with Drupal plugins, which may be intended to be
 * dormant unless certain other modules are installed.
 */
class TraitSafeClassLoader {

  protected bool $missingClass = FALSE;

  public function loadClass($class): void {
    if (str_ends_with($class, 'Trait')) {
      $this->missingClass = TRUE;
      class_alias(StubTrait::class, $class, TRUE);
    }
  }

  public function hasMissingClass(): bool {
    return $this->missingClass;
  }

  public function reset(): void {
    $this->missingClass = FALSE;
  }

}
