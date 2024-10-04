<?php

namespace Drupal\Core\File\MimeType;

use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Makes possible to guess the MIME type of a file using its extension.
 */
class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface {

  use DeprecatedServicePropertyTrait;

  /**
   * {@inheritdoc}
   */
  protected array $deprecatedProperties = [
    'moduleHandler' => 'module_handler',
  ];

  /**
   * The MIME type map.
   */
  protected MimeTypeMapInterface $map;

  /**
   * Constructs a new ExtensionMimeTypeGuesser.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|\Drupal\Core\File\MimeType\MimeTypeMapInterface $map
   *   The MIME type map.
   * @param \Drupal\Core\File\FileSystemInterface|null $fileSystem
   *   The file system.
   */
  public function __construct(
    ModuleHandlerInterface | MimeTypeMapInterface $map,
    protected ?FileSystemInterface $fileSystem = NULL,
  ) {
    if (!$map instanceof MimeTypeMapInterface) {
      @trigger_error(
        'Calling ' . __METHOD__ . '() with the $map argument as an instance of \Drupal\Core\Extension\ModuleHandlerInterface is deprecated in drupal:11.1.0 and an instance of \Drupal\Core\File\MimeType\MimeTypeMapInterface is required in drupal:11.0.0. See https://www.drupal.org/node/2311679',
        E_USER_DEPRECATED
      );
      $map = \Drupal::service('file.mime_type.map');
    }
    $this->map = $map;
    if (!$this->fileSystem) {
      @trigger_error(
        'Calling ' . __METHOD__ . '() without the $fileSystem argument is deprecated in drupal:11.1.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/2311679',
        E_USER_DEPRECATED
      );
      $this->fileSystem = \Drupal::service('file_system');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function guessMimeType($path): ?string {
    $extension = '';
    $file_parts = explode('.', $this->fileSystem->basename($path));

    // Remove the first part: a full filename should not match an extension.
    array_shift($file_parts);

    // Iterate over the file parts, trying to find a match.
    // For my.awesome.image.jpeg, we try:
    // - jpeg
    // - image.jpeg, and
    // - awesome.image.jpeg.
    while ($additional_part = array_pop($file_parts)) {
      $extension = strtolower($additional_part . ($extension ? '.' . $extension : ''));
      if ($mimeType = $this->map->getMimeTypeForExtension($extension)) {
        return $mimeType;
      }
    }

    return NULL;
  }

  /**
   * Sets the mimetypes/extension mapping to use when guessing mimetype.
   *
   * @param array|null $mapping
   *   Passing a NULL mapping will cause guess() to use self::$defaultMapping.
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use
   *   \Drupal\Core\File\MimeType\MimeTypeMapInterface::addMapping() instead.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  public function setMapping(?array $mapping = NULL): void {
    @trigger_error(
      __METHOD__ . '() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use \Drupal\Core\File\MimeType\MimeTypeMapInterface::addMapping() instead or define your own MimeTypeMapInterface implementation. See https://www.drupal.org/project/drupal/issues/2311679',
      E_USER_DEPRECATED
    );
    // Convert the mapping to be keyed by extension.
    $extensionMapping = $this->convertToExtensionKeyed($mapping);
    $this->map->clear();
    foreach ($extensionMapping as $type => $extensions) {
      foreach ($extensions as $extension) {
        $this->map->addMapping($type, $extension);
      }
    }
    // @phpstan-ignore-next-line
    \Drupal::service('file.mime_type.map_modifier.legacy_hook')
      ->modifyMimeTypeMap(
        $this->map
      );
  }

  /**
   * Converts the mapping to be keyed by extension.
   *
   * @param array $mapping
   *   The mapping to convert.
   *
   * @return array
   *   The converted mapping.
   */
  protected function convertToExtensionKeyed(array $mapping): array {
    $result = [];
    foreach ($mapping['mimetypes'] as $index => $mimetype) {
      $result[$mimetype] = array_keys($mapping['extensions'], $index);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function isGuesserSupported(): bool {
    return TRUE;
  }

}
