<?php

declare(strict_types=1);

namespace Drupal\Component\Discovery;

/**
 * Defines a classloader that throws an exception.
 *
 * This is not really a classloader, but exists to work around a PHP limitation
 * when it attempts to load a class that relies on a trait that does not exist.
 * This is a common situation with Drupal plugins, which may be intended to be
 * dormant unless certain other modules are installed.
 */
class TraitSafeClassLoader {

  /**
   * Flag indicating whether there was an attempt to load a missing trait.
   */
  protected bool $missingTrait = FALSE;

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
      $this->missingTrait = TRUE;
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
    return $this->missingTrait;
  }

  /**
   * Resets the missing trait flag to FALSE.
   */
  public function reset(): void {
    $this->missingTrait = FALSE;
  }

}
