<?php

declare(strict_types=1);

namespace Drupal\Component\Discovery;

/**
 * Defines a classloader that handles missing traits.
 *
 * This does not really load classes, but exists to work around a PHP limitation
 * when it attempts to load a class that relies on a trait that does not exist.
 * This is a common situation with Drupal plugins, which may be intended to be
 * dormant unless certain other modules are installed.
 */
class TraitSafeClassLoader {

  /**
   * Flag indicating whether there was an attempt to load a missing trait.
   */
  protected array $missingTraits = [];

  /**
   * Aliases trait to a stub trait and sets the missing trait flag.
   *
   * This method is registered as a class loader during attribute discovery and
   * runs last. Any call to this method means that $class is missing, and if
   * $class is a trait, the flag is set.
   *
   * @param string $class
   *   The classname to load.
   */
  public function loadClass(string $class): void {
    if (str_ends_with($class, 'Trait')) {
      $this->missingTraits[] = $class;
      class_alias(StubTrait::class, $class, TRUE);
    }
  }

  /**
   * Returns whether there was an attempt to load a missing trait.
   *
   * @return bool
   *   TRUE if there was an attempt to load a missing trait, otherwise FALSE.
   */
  public function hasMissingTrait(): bool {
    return \count($this->missingTraits) > 0;
  }

  /**
   * Returns all recorded missing traits since the last reset.
   *
   * @return string[]
   *   An array of traits recorded as missing.
   */
  public function getMissingTraits(): array {
    return $this->missingTraits;
  }

  /**
   * Resets the missing trait flag to FALSE.
   */
  public function reset(): void {
    $this->missingTraits = [];
  }

}
