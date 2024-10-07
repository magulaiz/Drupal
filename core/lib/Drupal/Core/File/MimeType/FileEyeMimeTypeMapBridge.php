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
   * {@inheritdoc}
   */
  public function addMapping(string $mimetype, string $extension): static {
    $this->mimeMap->addTypeExtensionMapping($mimetype, $extension);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeMapping(string $mimetype, string $extension): bool {
    return $this->mimeMap->removeTypeExtensionMapping($mimetype, $extension);
  }

  /**
   * {@inheritdoc}
   */
  public function removeMimeType(string $mimetype): bool {
    return $this->mimeMap->removeType($mimetype);
  }

  /**
   * {@inheritdoc}
   */
  public function listMimeTypes(): array {
    return $this->mimeMap->listTypes();
  }

  /**
   * {@inheritdoc}
   */
  public function getMimeTypeForExtension(string $extension): ?string {
    // Get the first mime type mapped.
    return $this->mimeMap->getExtensionTypes($extension)[0] ?? NULL;
  }

  /**
   * {@inheritdoc}
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
