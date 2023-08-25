<?php

namespace Drupal\file\Upload;

/**
 * An interface for handling individual file upload errors.
 */
interface FileUploadErrorHandlerInterface {

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
  public function handleError(UploadedFileInterface $uploadedFile, string $destination, \Exception $e): void;

}
