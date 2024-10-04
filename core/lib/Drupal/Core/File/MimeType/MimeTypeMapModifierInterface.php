<?php

declare(strict_types=1);

namespace Drupal\Core\File\MimeType;

/**
 * Interface for classes that modify the MIME type map.
 */
interface MimeTypeMapModifierInterface {

  /**
   * Modifies the MIME type map.
   */
  public function modifyMimeTypeMap(MimeTypeMapInterface $map): void;

}
