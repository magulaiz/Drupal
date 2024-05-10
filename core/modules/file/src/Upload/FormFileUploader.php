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
use Drupal\Core\Render\RendererInterface;
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
use Symfony\Component\Validator\ConstraintViolationListInterface;

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

  public function __construct(
    protected readonly MemoryCacheInterface $memoryCache,
    protected readonly FileUploadHandler $fileUploadHandler,
    protected readonly RequestStack $requestStack,
    protected readonly MessengerInterface $messenger,
    protected readonly RendererInterface $renderer,
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
   * @return array<\Drupal\file\Upload\FileUploadResult>
   *   The file upload results, or NULL if none were found.
   */
  public function saveFormUploadedFiles(string $uploadName, array $validators, string $destination = 'temporary://', FileExists $fileExists = FileExists::Rename, bool $addErrorMessages = TRUE): array {
    // Return cached objects without processing since the file will have
    // already been processed and the paths in $_FILES will be invalid.
    /** @var \Drupal\file\Upload\FileUploadResult[] $uploadResults */
    if ($cacheItem = $this->memoryCache->get($uploadName)) {
      return $cacheItem->data;
    }

    $request = $this->requestStack->getCurrentRequest();
    $uploadedFiles = UploadedFilesExtractor::extractUploadedFiles($request, $uploadName);

    $uploadResults = [];
    if (count($uploadedFiles) === 0) {
      return $uploadResults;
    }

    foreach ($uploadedFiles as $uploadedFile) {
      // Use a FormUploadedFile adapter to pass to FileUploadHandler.
      $formUploadedFile = new FormUploadedFile($uploadedFile);
      try {
        $result = $this->fileUploadHandler->handleFileUpload($formUploadedFile, $validators, $destination, $fileExists);
        if ($result->isRenamed()) {
          $this->messenger->addStatus($this->createRenameMessage($result));
        }
        if ($addErrorMessages && $result->hasViolations()) {
          $this->messenger->addError($this->createViolationMessage($formUploadedFile->getClientOriginalName(), $result->getViolations()));
        }
      }
      // Only catch exceptions that we can recover from.
      catch (SymfonyFileException | FileException | FileValidationException $e) {
        $error = $this->createErrorMessage($formUploadedFile, $destination, $e);
        $result = new FileUploadResult();
        $result->setOriginalFilename($uploadedFile->getClientOriginalName())
          ->setError($error);
        if ($addErrorMessages) {
          $this->messenger->addError($error);
        }
      }
      $uploadResults[] = $result;
    }

    // Add uploadResults to the cache.
    $this->memoryCache->set($uploadName, $uploadResults);

    return $uploadResults;
  }

  /**
   * Creates the violation messages.
   */
  protected function createViolationMessage(string $originalName, ConstraintViolationListInterface $violations): MarkupInterface {
    $items = [];
    foreach ($violations as $violation) {
      $items[] = $violation->getMessage();
    }
    $message = [
      'error' => [
        '#markup' => $this->t('The specified file %name could not be uploaded.', ['%name' => $originalName]),
      ],
      'item_list' => [
        '#theme' => 'item_list',
        '#items' => $items,
      ],
    ];
    return $this->renderer->renderInIsolation($message);
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
  protected function createRenameMessage(FileUploadResult $result): MarkupInterface {
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
