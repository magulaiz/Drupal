<?php

namespace Drupal\Core\File\MimeType;

use FileEye\MimeMap\Map\MimeMapInterface;

/**
 * A bridge between the FileEye MIME type map and the Drupal MIME type map.
 */
class FileEyeMimeTypeMapBridge implements MimeTypeMapInterface {

  public function __construct(
    protected MimeMapInterface $mimeMap,
  ) {}

  /**
   * {@inheritDoc}
   */
  public function addMapping(string $mimetype, string $extension): MimeTypeMapInterface {
    $this->mimeMap->addTypeExtensionMapping($mimetype, $extension);
  }

  /**
   * {@inheritDoc}
   */
  public function removeMapping(string $mimetype, string $extension): bool {
    return $this->mimeMap->removeTypeExtensionMapping($mimetype, $extension);
  }

  /**
   * {@inheritDoc}
   */
  public function removeMimeType(string $mimetype): bool {
    return $this->mimeMap->removeType($mimetype);
  }

  /**
   * {@inheritDoc}
   */
  public function listMimeTypes(): array {
    return $this->mimeMap->listTypes();
  }

  /**
   * {@inheritDoc}
   */
  public function getMimeTypeForExtension(string $extension): ?string {
    // Get the first mime type mapped.
    return $this->mimeMap->getExtensionTypes($extension)[0] ?? NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getExtensionsForMimeType(string $mimetype): array {
    return $this->mimeMap->getTypeExtensions($mimetype);
  }

  /**
   * {@inheritdoc}
   */
  public function listExtensions(): array {
    return $this->mimeMap->listExtensions();
  }

  /**
   * {@inheritdoc}
   */
  public function hasMimeType(string $mimetype): bool {
    return $this->mimeMap->hasType($mimetype);
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtension(string $extension): bool {
    return $this->mimeMap->hasExtension($extension);
  }

}
