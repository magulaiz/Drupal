<?php

namespace Drupal\Composer\Plugin\Unpack\Unpackers;

/**
 * Interface for dependency unpackers.
 */
interface UnpackerInterface {

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
