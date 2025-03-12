<?php

declare(strict_types=1);

namespace Drupal\Component\Discovery;

/**
 * Defines a classloader that detects missing classes.
 *
 * This does not really load classes, but allows calling code to explicitly
 * check whether a class that was requested failed to discovered by other
 * classloaders.
 *
 * It also works around a PHP limitation when it attempts to load a class that
 * relies on a trait that does not exist. This is a common situation with Drupal
 * plugins, which may be intended to be  dormant unless certain other modules
 * are installed.
 *
 * @see https://github.com/php/php-src/issues/17959
 * @internal
 */
final class MissingClassDetectionClassLoader {

  /**
   * Flag indicating whether there was an attempt to load a missing class.
   */
  protected array $missingClasses = [];

  /**
   * Records missing classes and aliases missing traits.
   *
   * This method is registered as a class loader during attribute discovery and
   * runs last. Any call to this method means that $class is missing, and if
   * $class is a trait, it is aliased to a stub trait to avoid an uncatchable
   * PHP fatal error.
   *
   * @param string $class
   *   The classname to load.
   */
  public function loadClass(string $class): void {
      $this->missingClasses[] = $class;
    if (str_ends_with($class, 'Trait')) {
      class_alias(StubTrait::class, $class, TRUE);
    }
  }

  /**
   * Returns whether there was an attempt to load a missing trait.
   *
   * @return bool
   *   TRUE if there was an attempt to load a missing trait, otherwise FALSE.
   */
  public function hasMissingClass(): bool {
    return \count($this->missingClasses) > 0;
  }

  /**
   * Returns all recorded missing classes since the last reset.
   *
   * @return string[]
   *   An array of traits recorded as missing.
   */
  public function getMissingClasses(): array {
    return $this->missingClasses;
  }

  /**
   * Resets the missing classes flag to FALSE.
   */
  public function reset(): void {
    $this->missingClasses = [];
  }

}
