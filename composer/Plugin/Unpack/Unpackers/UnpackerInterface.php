<?php

namespace Drupal\Composer\Plugin\Unpack\Unpackers;

/**
 * Interface for dependency unpackers.
 */
interface UnpackerInterface {

  /**
   * Unpacks the package dependencies.
   */
  public function unpackDependencies(): void;

  /**
   * Determines if the unpacker should be removed from the root composer.json.
   *
   * @return bool
   *   TRUE if the unpacker should be removed from the root composer.json.
   */
  public function removeSelf(): bool;

}
