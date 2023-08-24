<?php

namespace Drupal\file\Upload;

use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;

/**
 * Helper class for multiple form file uploads.
 *
 * This class provides API-level methods for uploading multiple files from a
 * form.
 *
 * For Form API elements or user-facing Messenger messages, use
 * \Drupal\file\Upload\FileElementHelper instead.
 *
 * @see \Drupal\file\Upload\FileElementHelper
 */
class FormFileUploadHandler {

  /**
   * Constructs a FormFileUploader object.
   */
  public function __construct(
    protected MemoryCacheInterface $memoryCache,
    protected FileUploadHandler $fileUploadHandler,
    protected FormUploadedFileRetriever $uploadedFileRetriever,
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Saves file uploads to a new location.
   *
   * The files will be added to the {file_managed} table as temporary files.
   * Temporary files are periodically cleaned. Use the 'file.usage' service to
   * register the usage of the file which will automatically mark it as
   * permanent.
   *
   * Note that this function does not support correct form error handling. The
   * file upload widgets in core do support this. It is advised to use these in
   * any custom form, instead of calling this function.
   *
   * @param string $uploadKey
   *   The key of the upload form element in the form array.
   * @param array $validators
   *   (optional) An associative array of callback functions used to validate
   *   the file. See file_validate() for a full discussion of the array format.
   *   If the array is empty, it will be set up to validate the extension with
   *   a safe list of extensions, as follows: "jpg jpeg gif png txt doc xls pdf
   *   ppt pps odt ods odp". To allow all extensions, you must explicitly set
   *   this array to ['file_validate_extensions' => '']. (Beware: this is not
   *   safe and should only be allowed for trusted users, if at all.)
   * @param callable $errorHandler
   *   The error handler. This is a callable that accepts three arguments:
   *    - \Drupal\file\Upload\UploadedFileInterface $uploadedFile
   *    - string $destination
   *    - \Exception $e.
   * @param string $destination
   *   (optional) A string containing the URI that the file should be copied
   *   to.
   *   This must be a stream wrapper URI. If this value is omitted or set to
   *   NULL, Drupal's temporary files scheme will be used ("temporary://").
   * @param string $replace
   *   (optional) The replace behavior when the destination file already
   *   exists.
   *   Possible values include:
   *   - FileSystemInterface::EXISTS_REPLACE: Replace the existing file.
   *   - FileSystemInterface::EXISTS_RENAME: (default) Append
   *     _{incrementing number} until the filename is unique.
   *   - FileSystemInterface::EXISTS_ERROR: Do nothing and return FALSE.
   *
   * @return \Drupal\file\FileInterface[]
   *   An array of files..
   */
  public function saveFileUploads(string $uploadKey, array $validators, callable $errorHandler, ?string $destination = 'temporary://', string $replace = FileSystemInterface::EXISTS_RENAME): array {
    // Return cached objects without processing since the file will have
    // already been processed and the paths in $_FILES will be invalid.
    /** @var \Drupal\file\FileInterface[] $files */
    if ($files = $this->memoryCache->get($uploadKey)) {
      return $files;
    }

    $uploadedFiles = $this->uploadedFileRetriever->getUploadedFiles($uploadKey);

    if (!$destination) {
      $destination = 'temporary://';
    }

    $files = [];
    foreach ($uploadedFiles as $i => $uploadedFile) {
      // Use a FormUploadedFile adapter to pass to FileUploadHandler.
      $formUploadedFile = new FormUploadedFile($uploadedFile);
      $result = NULL;
      try {
        $result = $this->fileUploadHandler->handleFileUpload($formUploadedFile, $validators, $destination, $replace);
        $files[$i] = $result->getFile();
      }
      // Only catch exceptions that we can recover from.
      catch (SymfonyFileException | FileException | FileValidationException $e) {
        call_user_func_array($errorHandler, [
          $formUploadedFile,
          $destination,
          $e,
        ]);
        // Set to keep the array index in sync.
        $files[$i] = NULL;
      }
    }
    // Add files to the cache.
    $this->memoryCache->set($uploadKey, $files);

    return $files;
  }

}
