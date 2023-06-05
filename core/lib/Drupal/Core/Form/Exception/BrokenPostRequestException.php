<?php

declare(strict_types=1);

namespace Drupal\Core\Form\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Defines an exception used, when the POST HTTP body is broken.
 */
class BrokenPostRequestException extends BadRequestHttpException {

  /**
   * The maximum upload size.
   *
   * @var int|float
   */
  protected int|float $size;

  /**
   * Constructs a new BrokenPostRequestException.
   *
   * @param int|float $max_upload_size
   *   The size of the maximum upload size in bytes.
   * @param string $message
   *   The internal exception message.
   * @param \Exception|null $previous
   *   The previous exception.
   * @param int $code
   *   The internal exception code.
   */
  public function __construct(int|float $max_upload_size, string $message = '', \Exception $previous = NULL, int $code = 0) {
    parent::__construct($message, $previous, $code);

    $this->size = $max_upload_size;
  }

  /**
   * Returns the maximum upload size in bytes.
   *
   * @return int|float
   *   The file size limit in bytes based on the PHP upload_max_filesize and
   *   post_max_size.
   */
  public function getSize(): int|float {
    return $this->size;
  }

}
