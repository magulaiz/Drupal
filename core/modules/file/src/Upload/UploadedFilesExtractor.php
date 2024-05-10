<?php

namespace Drupal\file\Upload;

use Symfony\Component\HttpFoundation\Request;

/**
 * Helper class for getting the uploaded files for a given form element name.
 */
class UploadedFilesExtractor {

  /**
   * Extracts the uploaded files from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $name
   *   The form element name.
   *
   * @return \Symfony\Component\HttpFoundation\File\UploadedFile[]
   *   The uploaded files.
   */
  public static function extractUploadedFiles(Request $request, string $name): array {
    $allFiles = $request->files->get('files', []);
    if (empty($allFiles[$name])) {
      return [];
    }

    // Ensure we return an array.
    $uploadedFiles = $allFiles[$name];
    if (!is_array($uploadedFiles)) {
      $uploadedFiles = [$uploadedFiles];
    }
    return $uploadedFiles;
  }

}
