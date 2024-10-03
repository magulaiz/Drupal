<?php

namespace Drupal\Core\File\MimeType;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a sensible mapping between filename extensions and MIME types.
 */
interface MimeTypeMapInterface {

  /**
   * Allow modules to alter the default mapping.
   *
   * Invokes hook_file_mimetype_mapping_alter().
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   *
   * @return $this
   *
   * @see hook_file_mimetype_mapping_alter()
   */
  public function alterMapping(ModuleHandlerInterface $module_handler): self;

  /**
   * Resets the mapping to its initial state.
   */
  public function reset(): void;

  /**
   * Adds a mapping between a MIME type and an extension.
   *
   * @param string $mimetype
   *   The MIME type the passed extension should map.
   * @param string|string[] $extensions
   *   The extension(s) that should map to the passed MIME type.
   *
   * @return $this
   */
  public function addMapping(
    string $mimetype,
    string | array $extensions,
  ): self;

  /**
   * Removes the mapping between a MIME type and an extension.
   *
   * @param string $extension
   *   The extension to be removed from the mapping.
   *
   * @return bool
   *   TRUE if the extension was present, FALSE otherwise.
   */
  public function removeMapping(string $extension): bool;

  /**
   * Removes a MIME type and all its mapped extensions from the mapping.
   *
   * @param string $mimetype
   *   The MIME type to be removed from the mapping.
   *
   * @return bool
   *   TRUE if the MIME type was present, FALSE otherwise.
   */
  public function removeMimeType(string $mimetype): bool;

  /**
   * Returns known MIME types.
   *
   * @return string[]
   *   An array of MIME types.
   */
  public function getMimeTypes(): array;

  /**
   * Returns the appropriate MIME type for a given file extension.
   *
   * @param string $extension
   *   A file extension, without leading dot.
   *
   * @return string|null
   *   A matching MIME type, or NULL if no MIME type matches the extension.
   */
  public function getMimeTypeForExtension(string $extension): ?string;

  /**
   * Returns the appropriate extensions for a given MIME type.
   *
   * @param string $mimetype
   *   A MIME type.
   *
   * @return string[]
   *   An array of file extensions matching the MIME type, without leading dot.
   */
  public function getExtensionsForMimeType(string $mimetype): array;

}
