<?php

namespace Drupal\Composer\Plugin\Unpack\Unpackers;

/**
 * Interface for dependency unpackers.
 */
interface UnpackerInterface {

  /**
   * Get the ID of the unpacker.
   *
   * @return string
   *   The ID of the unpacker.
   */
  public function id(): string;

  /**
   * Unpack the package dependencies.
   */
  public function unpackDependencies(): void;

  /**
   * Whether the unpacker should remove itself from the root composer.json.
   *
   * @return bool
   *   TRUE if the unpacker should remove itself from the root composer.json.
   */
  public function removeSelf(): bool;

}
