<?php

declare(strict_types=1);

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;

/**
 * Defines a class for file upload results.
 *
 * This class uses the array index of the original uploaded files array to
 * allow upload results and errors to be associated correctly.
 */
class FileUploadResults {

  /**
   * The results, indexed matching the uploaded files array index.
   *
   * @var array<int,\Drupal\file\Upload\FileUploadResult|false>
   */
  protected array $results = [];

  /**
   * The errors, indexed matching the uploaded files array index.
   *
   * @var array<int,\Drupal\Component\Render\MarkupInterface|false>
   */
  protected array $errors = [];

  /**
   * Adds a result at the index.
   *
   * @param int $index
   *   The array index.
   * @param \Drupal\file\Upload\FileUploadResult|false $result
   *   The result, or FALSE if no result.
   */
  public function setResult(int $index, FileUploadResult|false $result): void {
    $this->results[$index] = $result;
  }

  /**
   * Gets the result at the index.
   *
   * @param int $index
   *   The index.
   *
   * @return \Drupal\file\Upload\FileUploadResult|false
   *   The result, or FALSE if no result.
   */
  public function getResult(int $index): FileUploadResult|false {
    return $this->results[$index] ?? FALSE;
  }

  /**
   * Gets the file upload results.
   *
   * @return array<int,\Drupal\file\Upload\FileUploadResult|false>
   *   The file upload results.
   */
  public function getResults(): array {
    return $this->results;
  }

  /**
   * Adds an error.
   *
   * @param int $index
   *   The array index.
   * @param \Drupal\Component\Render\MarkupInterface|false $error
   *   The error, or FALSE if no error.
   */
  public function setError(int $index, MarkupInterface|false $error): void {
    $this->errors[$index] = $error;
  }

  /**
   * Gets the error at the index.
   *
   * @param int $index
   *   The index.
   *
   * @return \Drupal\Component\Render\MarkupInterface|false
   *   The error, or FALSE if no error.
   */
  public function getError(int $index): MarkupInterface|false {
    return $this->errors[$index] ?? FALSE;
  }

  /**
   * Gets the errors.
   *
   * @return array<int,\Drupal\Component\Render\MarkupInterface|false>
   *   The errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

}
