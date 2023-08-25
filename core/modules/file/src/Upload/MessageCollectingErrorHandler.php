<?php

namespace Drupal\file\Upload;

use Drupal\Component\Utility\Environment;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileExistsException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\Exception\FormSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use Symfony\Component\HttpFoundation\File\Exception\NoFileException;
use Symfony\Component\HttpFoundation\File\Exception\PartialFileException;

/**
 * An error callback that collects error messages for later retrieval.
 */
class MessageCollectingErrorHandler implements FileUploadErrorHandlerInterface {

  use StringTranslationTrait;

  /**
   * The error messages.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup[]
   */
  protected array $errors = [];

  /**
   * The logger.
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new MessageCollectingErrorHandler object.
   */
  public function __construct(LoggerInterface $logger = NULL) {
    if (!$logger) {
      $logger = \Drupal::logger('file');
    }
    $this->logger = $logger;
  }

  /**
   * Handles the file upload error.
   *
   * @param \Drupal\file\Upload\UploadedFileInterface $uploadedFile
   *   The uploaded file.
   * @param string $destination
   *   The destination.
   * @param \Exception $e
   *   The exception that was caught.
   */
  public function handleError(UploadedFileInterface $uploadedFile, string $destination, \Exception $e): void {
    switch ($e) {
      case $e instanceof FileExistsException:
        $this->errors[] = $this->t('Destination file "%file" exists', ['%file' => $destination . $uploadedFile->getFilename()]);
        break;

      case $e instanceof InvalidStreamWrapperException:
        $this->errors[] = $this->t('The file could not be uploaded because the destination "%destination" is invalid.', ['%destination' => $destination]);
        break;

      case $e instanceof IniSizeFileException:
      case $e instanceof FormSizeFileException:
        $this->errors[] = $this->t('The file %file could not be saved because it exceeds %maxsize, the maximum allowed size for uploads.', [
          '%file' => $uploadedFile->getFilename(),
          '%maxsize' => ByteSizeMarkup::create(Environment::getUploadMaxSize()),
        ]);
        break;

      case $e instanceof PartialFileException:
      case $e instanceof NoFileException:
        $this->errors[] = $this->t('The file %file could not be saved because the upload did not complete.', [
          '%file' => $uploadedFile->getFilename(),
        ]);
        break;

      case $e instanceof SymfonyFileException:
        $this->errors[] = $this->t('The file %file could not be saved. An unknown error has occurred.', [
          '%file' => $uploadedFile->getFilename(),
        ]);
        break;

      case $e instanceof FileValidationException:
        foreach ($e->getErrors() as $error) {
          $this->errors[] = $error;
        }
        break;

      case $e instanceof FileWriteException:
        $this->errors[] = $this->t('File upload error. Could not move uploaded file.');
        $this->logger->notice('Upload error. Could not move uploaded file %file to destination %destination.', [
          '%file' => $uploadedFile->getClientOriginalName(),
          '%destination' => $destination . '/' . $uploadedFile->getClientOriginalName(),
        ]);
        break;

      case $e instanceof FileException:
        $this->errors[] = $this->t('The file %filename could not be uploaded because the name is invalid.', [
          '%filename' => $uploadedFile->getClientOriginalName(),
        ]);
        break;
    }
  }

  /**
   * Gets the error messages.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   The errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

}
