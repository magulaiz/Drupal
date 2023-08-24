<?php

namespace Drupal\file\Upload;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper class for getting the uploaded files for a given form element name.
 */
class FormUploadedFileRetriever {

  /**
   * Constructs a FormUploadedFileRetriever object.
   */
  public function __construct(
    protected RequestStack $requestStack,
  ) {}

  /**
   * Returns the uploaded files for a given form element name.
   *
   * @param string $name
   *   The form element name.
   *
   * @return \Symfony\Component\HttpFoundation\File\UploadedFile[]
   *   The uploaded files.
   */
  public function getUploadedFiles(string $name): array {
    $allFiles = $this->requestStack->getCurrentRequest()->files->get('files', []);
    if (empty($allFiles[$name])) {
      return [];
    }

    // Need to make sure we return an array.
    $uploadedFiles = $allFiles[$name];
    if (!is_array($uploadedFiles)) {
      $uploadedFiles = [$uploadedFiles];
    }
    return $uploadedFiles;
  }

}
