<?php

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;

/**
 * An error handler that collects error messages for later retrieval.
 */
class MessageCollectingErrorHandler extends BaseErrorHandler {

  /**
   * The error messages.
   *
   * @var \Drupal\Component\Render\MarkupInterface[]
   */
  protected array $errors = [];

  /**
   * {@inheritdoc}
   */
  protected function addErrorMessage(MarkupInterface $message): void {
    $this->errors[] = $message;
  }

  /**
   * Gets the error messages.
   *
   * @return \Drupal\Component\Render\MarkupInterface[]
   *   The errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

}
