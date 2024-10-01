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
   * The MIME types mapper service.
   */
  protected MimeTypeMapperInterface $mapper;

  /**
   * Constructs a new ExtensionMimeTypeGuesser.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|\Drupal\Core\File\MimeType\MimeTypeMapperInterface $mapper
   *   The MIME types mapper service.
   * @param \Drupal\Core\File\FileSystemInterface|null $fileSystem
   *   The file system.
   */
  public function __construct(
    ModuleHandlerInterface | MimeTypeMapperInterface $mapper,
    protected ?FileSystemInterface $fileSystem = NULL,
  ) {
    if (!$mapper instanceof MimeTypeMapperInterface) {
      @trigger_error(
        'Calling ' . __METHOD__ . '() with the $mapper argument as an instance of \Drupal\Core\Extension\ModuleHandlerInterface is deprecated in drupal:11.1.0 and an instance of \Drupal\Core\File\MimeType\MimeTypeMapperInterface is required in drupal:11.0.0. See https://www.drupal.org/node/2311679',
        E_USER_DEPRECATED
      );
      $mapper = \Drupal::service('file.mime_type.mapper');
    }
    $this->mapper = $mapper;
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
      if ($mimeType = $this->mapper->getMimeTypeForExtension($extension)) {
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
   *   \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  public function setMapping(?array $mapping = NULL): void {
    @trigger_error(
      __METHOD__ . '() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead. See https://www.drupal.org/project/drupal/issues/2311679',
      E_USER_DEPRECATED
    );
    $this->mapper->setMapping($mapping);
    // @phpstan-ignore-next-line property.notFound
    $this->mapper->alterMapping($this->moduleHandler);
  }

  /**
   * {@inheritdoc}
   */
  public function isGuesserSupported(): bool {
    return TRUE;
  }

}
