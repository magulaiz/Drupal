<?php

namespace Drupal\file\Upload;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;

/**
 * Helper class for ManagedFile element form file uploads.
 *
 * This class handles the Form API and user-facing messages.
 *
 * For API-level form file uploading, use
 * \Drupal\file\Upload\FormFileUploadHandler instead.
 *
 * @see \Drupal\file\Upload\FormFileUploadHandler
 * @see \Drupal\file\Element\ManagedFile::valueCallback()
 */
class FileElementHelper {

  use StringTranslationTrait;

  /**
   * Constructs a FileFormHelper object.
   */
  public function __construct(
    protected FileSystemInterface $fileSystem,
    protected LoggerInterface $logger,
    protected FormFileUploadHandler $formUploadHandler,
    protected FormUploadedFileRetriever $uploadedFileRetriever,
    protected MessageCollectingErrorHandlerFactory $errorHandlerFactory,
  ) {}

  /**
   * Saves any files that have been uploaded into a managed_file element.
   *
   * @param array $element
   *   The FAPI element whose values are being saved.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   *
   * @return array
   *   An array of file entities for each file that was saved, keyed by its file
   *   ID. Each array element contains a file entity.
   */
  public function saveFileUploads(array $element, FormStateInterface $formState): array {
    $uploadName = implode('_', $element['#parents']);
    $uploadedFiles = $this->uploadedFileRetriever->getUploadedFiles($uploadName);

    // Check for uploads.
    $hasUploads = $element['#multiple'] && count(array_filter($uploadedFiles)) > 0;
    $hasUploads |= !$element['#multiple'] && count($uploadedFiles) > 0;

    if (!$hasUploads) {
      return [];
    }

    $destination = $element['#upload_location'] ?? NULL;
    if (isset($destination) && !$this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY)) {
      $this->logger->notice('The upload directory %directory for the file field %name could not be created or is not accessible. A newly uploaded file could not be saved in this directory as a consequence, and the upload was canceled.', [
        '%directory' => $destination,
        '%name' => $element['#field_name'],
      ]);
      $formState->setError($element, $this->t('The file could not be uploaded.'));
      return [];
    }

    $validators = $element['#upload_validators'] ?? [];

    $collectingErrorHandler = $this->errorHandlerFactory->create();

    $files = $this->formUploadHandler->saveFileUploads(
      uploadName: $uploadName,
      validators: $validators,
      destination: $destination,
      errorHandler: $collectingErrorHandler
    );

    $files = array_filter($files);

    // Add any collected error messages to the form.
    $errors = $collectingErrorHandler->getErrors();
    if (count($errors) > 0) {
      if (count($errors) === 1) {
        // Use the first error message as the form error.
        $message = reset($errors);
      }
      else {
        // Combine the error messages into a list.
        $message = [
          'error' => [
            '#markup' => $this->t('One or more files could not be uploaded.'),
          ],
          'item_list' => [
            '#theme' => 'item_list',
            '#items' => $errors,
          ],
        ];
      }
      $formState->setError($element, $message);
    }

    if (count($files) === 0) {
      $this->logger->notice('The file upload failed. %upload', [
        '%upload' => $uploadName,
      ]);
      return [];
    }

    // Value callback expects FIDs to be keys.
    $fids = array_map(fn($file) => $file->id(), $files);
    return array_combine($fids, $files);
  }

}
