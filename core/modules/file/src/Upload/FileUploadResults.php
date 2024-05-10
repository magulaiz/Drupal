<?php

declare(strict_types=1);

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;

/**
 * Defines a class for file upload results.
 */
class FileUploadResults {

  /**
   * The results.
   *
   * @var \Drupal\file\Upload\FileUploadResult[]
   */
  protected array $results = [];

  /**
   * The errors.
   *
   * @var \Drupal\Component\Render\MarkupInterface[]
   */
  protected array $errors = [];

  /**
   * Adds a result at the index.
   *
   * @param int $index
   *   The array index.
   * @param \Drupal\file\Upload\FileUploadResult $result
   *   The result.
   */
  public function addResultAt(int $index, FileUploadResult $result): void {
    $this->results[$index] = $result;
  }

  /**
   * Gets the result at the index.
   *
   * @param int $index
   *   The index.
   *
   * @return \Drupal\file\Upload\FileUploadResult|null
   *   The result or NULL if not found.
   */
  public function getResultAt(int $index): ?FileUploadResult {
    return $this->results[$index] ?? NULL;
  }

  /**
   * Gets the file upload results.
   *
   * @return \Drupal\file\Upload\FileUploadResult[]
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
   * @param \Drupal\Component\Render\MarkupInterface $error
   *   The error.
   */
  public function addErrorAt(int $index, MarkupInterface $error): void {
    $this->errors[$index] = $error;
  }

  /**
   * Gets the errors.
   *
   * @return \Drupal\Component\Render\MarkupInterface[]
   *   The errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

}
