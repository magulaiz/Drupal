<?php

namespace Drupal\Core\File\MimeType;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Makes possible to guess the MIME type of a file using its extension.
 */
class ExtensionMimeTypeGuesser implements MimeTypeGuesserInterface {

  /**
   * Default MIME extension mapping.
   *
   * @var array
   *   Array of mimetypes correlated to the extensions that relate to them.
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. No
   *   replacement provided.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  protected $defaultMapping = [];

  /**
   * The MIME types mapping array after going through the module handler.
   *
   * @var array
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. No
   *   replacement provided.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  protected $mapping;

  /**
   * The MIME types mapper service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeMapperInterface
   */
  protected $mapper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. No
   *   replacement provided.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  protected $moduleHandler;

  /**
   * Constructs a new ExtensionMimeTypeGuesser.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\MimeTypeMapperInterface $mapper
   *   The MIME types mapper service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, MimeTypeMapperInterface $mapper = NULL) {
    if (!$mapper) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $mapper argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/2311679.', E_USER_DEPRECATED);
      $mapper = \Drupal::service('file.mime_type.mapper');
    }
    else {
      $this->mapper = $mapper;
    }
    // @todo remove below lines in Drupal 11.0.0.
    $this->moduleHandler = $module_handler;
    $this->defaultMapping = $this->mapper->getDefaultMapping();
    $this->mapping = $this->mapper->getMapping();
  }

  /**
   * {@inheritdoc}
   */
  public function guessMimeType($path): ?string {
    if ($this->mapping === NULL) {
      $mapping = $this->defaultMapping;
      // Allow modules to alter the default mapping.
      $this->moduleHandler->alter('file_mimetype_mapping', $mapping);
      $this->mapping = $mapping;
    }

    $extension = '';
    $file_parts = explode('.', \Drupal::service('file_system')->basename($path));

    // Remove the first part: a full filename should not match an extension.
    array_shift($file_parts);

    // Iterate over the file parts, trying to find a match.
    // For my.awesome.image.jpeg, we try:
    // - jpeg
    // - image.jpeg, and
    // - awesome.image.jpeg.
    while ($additional_part = array_pop($file_parts)) {
      $extension = strtolower($additional_part . ($extension ? '.' . $extension : ''));
      if (isset($this->mapping['extensions'][$extension])) {
        return $this->mapping['mimetypes'][$this->mapping['extensions'][$extension]];
      }
    }

    return 'application/octet-stream';
  }

  /**
   * Sets the mimetypes/extension mapping to use when guessing mimetype.
   *
   * @param array|null $mapping
   *   Passing a NULL mapping will cause guess() to use self::$defaultMapping.
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead.
   *
   * @see https://www.drupal.org/project/drupal/issues/2311679
   */
  public function setMapping(array $mapping = NULL) {
    @trigger_error(__CLASS__ . '::setMapping() is deprecated in drupal:10.1.0, and will be removed in drupal:11.0.0. Use \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead. See https://www.drupal.org/project/drupal/issues/2311679.', E_USER_DEPRECATED);
    $this->mapper->setMapping($mapping);
    $this->mapping = $mapping;
  }

  /**
   * {@inheritdoc}
   */
  public function isGuesserSupported(): bool {
    return TRUE;
  }

}
