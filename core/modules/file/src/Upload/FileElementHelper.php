<?php

namespace Drupal\file\Upload;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
    protected RequestStack $requestStack,
    protected FileSystemInterface $fileSystem,
    protected LoggerInterface $logger,
    protected MessengerInterface $messenger,
    protected RendererInterface $renderer,
    protected FormFileUploadHandler $formUploadHandler,
    protected FormUploadedFileRetriever $uploadedFileRetriever,
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
    $errorCallback = new MessageCollectingErrorCallback($this->logger);

    $files = $this->formUploadHandler->saveFileUploads($uploadName, $validators, $errorCallback->onError(...), $destination);

    $files = array_filter($files);

    if (count($errorCallback->getErrors()) > 0) {
      $formState->setError($element, $this->renderErrorMessage($errorCallback->getErrors()));
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

  /**
   * Renders an error message from multiple errors.
   *
   * This is needed because only one error per element is supported.
   *
   * @param array $errors
   *   The errors to render.
   *
   * @return string
   *   The rendered error message.
   */
  protected function renderErrorMessage(array $errors): string {
    $render_array = [
      'error' => [
        '#markup' => $this->t('One or more files could not be uploaded.'),
      ],
      'item_list' => [
        '#theme' => 'item_list',
        '#items' => $errors,
      ],
    ];
    return $this->renderer->renderPlain($render_array);
  }

}
