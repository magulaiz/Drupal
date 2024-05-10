<?php

declare(strict_types=1);

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileExists;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;
use Symfony\Component\HttpFoundation\RequestStack;

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
class FormFileUploader {

  use StringTranslationTrait;

  /**
   * Constructs a FormFileUploader object.
   */
  public function __construct(
    protected readonly MemoryCacheInterface $memoryCache,
    protected readonly FileUploadHandler $fileUploadHandler,
    protected readonly RequestStack $requestStack,
    protected readonly MessengerInterface $messenger,
    #[AutowireServiceClosure('logger.channel.file')]
    protected readonly \Closure $logger,
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
   * @param string $uploadName
   *   The key of the upload form element in the form array.
   * @param array $validators
   *   (optional) An associative array of callback functions used to validate
   *   the file. See file_validate() for a full discussion of the array format.
   *   If the array is empty, it will be set up to validate the extension with
   *   a safe list of extensions, as follows: "jpg jpeg gif png txt doc xls pdf
   *   ppt pps odt ods odp". To allow all extensions, you must explicitly set
   *   this array to ['file_validate_extensions' => '']. (Beware: this is not
   *   safe and should only be allowed for trusted users, if at all.)
   * @param string $destination
   *   (optional) A string containing the URI that the file should be copied
   *   to.
   *   This must be a stream wrapper URI. If this value is omitted or set to
   *   NULL, Drupal's temporary files scheme will be used ("temporary://").
   * @param \Drupal\Core\File\FileExists $fileExists
   *   (optional) The replace behavior when the destination file already
   *   exists.
   * @param bool $addErrorMessages
   *   (optional) Whether to add error messages to the messenger. Defaults to
   *   TRUE.
   *
   * @return \Drupal\file\Upload\FileUploadResults|null
   *   The file upload results, or NULL if none were found.
   */
  public function saveFormUploadedFiles(string $uploadName, array $validators, string $destination = 'temporary://', FileExists $fileExists = FileExists::Rename, bool $addErrorMessages = TRUE): ?FileUploadResults {
    // Return cached objects without processing since the file will have
    // already been processed and the paths in $_FILES will be invalid.
    /** @var \Drupal\file\Upload\FileUploadResult[] $uploadResults */
    if ($cacheItem = $this->memoryCache->get($uploadName)) {
      return $cacheItem->data;
    }

    $request = $this->requestStack->getCurrentRequest();
    $uploadedFiles = UploadedFilesExtractor::extractUploadedFiles($request, $uploadName);

    if (count($uploadedFiles) === 0) {
      return NULL;
    }

    $uploadResults = new FileUploadResults();
    foreach ($uploadedFiles as $i => $uploadedFile) {
      // Use a FormUploadedFile adapter to pass to FileUploadHandler.
      $formUploadedFile = new FormUploadedFile($uploadedFile);
      try {
        $result = $this->fileUploadHandler->handleFileUpload($formUploadedFile, $validators, $destination, $fileExists);
        if ($result->isRenamed()) {
          $this->messenger->addStatus($this->getRenameMessage($result));
        }
        $uploadResults->addResultAt($i, $result);
      }
      // Only catch exceptions that we can recover from.
      catch (SymfonyFileException | FileException | FileValidationException $e) {
        $error = $this->createErrorMessage($formUploadedFile, $destination, $e);
        $uploadResults->addErrorAt($i, $error);
        if ($addErrorMessages) {
          $this->messenger->addError($error);
        }
      }
    }

    // Add uploadResults to the cache.
    $this->memoryCache->set($uploadName, $uploadResults);

    return $uploadResults;
  }

  /**
   * Creates a formatted error messages for a file upload exception.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The error message.
   */
  protected function createErrorMessage(FormUploadedFile $uploadedFile, string $destination, \Exception $e): MarkupInterface {
    switch ($e) {
      case $e instanceof FileExistsException:
        return $this->t('Destination file "%file" exists', ['%file' => $destination . $uploadedFile->getFilename()]);

      case $e instanceof InvalidStreamWrapperException:
        return $this->t('The file could not be uploaded because the destination "%destination" is invalid.', ['%destination' => $destination]);

      case $e instanceof IniSizeFileException:
      case $e instanceof FormSizeFileException:
        return $this->t('The file %file could not be saved because it exceeds %maxsize, the maximum allowed size for uploads.', [
          '%file' => $uploadedFile->getFilename(),
          '%maxsize' => ByteSizeMarkup::create(Environment::getUploadMaxSize()),
        ]);

      case $e instanceof PartialFileException:
      case $e instanceof NoFileException:
        return $this->t('The file %file could not be saved because the upload did not complete.', [
          '%file' => $uploadedFile->getFilename(),
        ]);

      case $e instanceof SymfonyFileException:
        return $this->t('The file %file could not be saved. An unknown error has occurred.', [
          '%file' => $uploadedFile->getFilename(),
        ]);

      case $e instanceof FileWriteException:
        $this->getLogger()->notice('Upload error. Could not move uploaded file %file to destination %destination.', [
          '%file' => $uploadedFile->getClientOriginalName(),
          '%destination' => $destination . '/' . $uploadedFile->getClientOriginalName(),
        ]);
        return $this->t('File upload error. Could not move uploaded file.');

      case $e instanceof FileException:
        return $this->t('The file %filename could not be uploaded because the name is invalid.', [
          '%filename' => $uploadedFile->getClientOriginalName(),
        ]);

      default:
        return $this->t('The specified file %name could not be uploaded.', ['%name' => $uploadedFile->getClientOriginalName()]);
    }

  }

  /**
   * Creates the rename messages.
   *
   * @param \Drupal\file\Upload\FileUploadResult $result
   *   The result.
   */
  protected function getRenameMessage(FileUploadResult $result): MarkupInterface {
    // If the filename has been modified, let the user know.
    $filename = $result->getFile()->getFilename();
    if ($result->isSecurityRename()) {
      return $this->t('For security reasons, your upload has been renamed to %filename.',
        ['%filename' => $filename]
      );
    }
    else {
      return $this->t(
        'Your upload has been renamed to %filename.',
        ['%filename' => $filename]
      );
    }
  }

  /**
   * Gets the logger.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger.
   */
  private function getLogger(): LoggerInterface {
    return ($this->logger)();
  }

}
