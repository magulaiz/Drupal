<?php

namespace Drupal\file\Upload;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event that is fired when a file is uploaded.
 */
class FileUploadedEvent extends Event {

  /**
   * Constructs a new FileUploadedEvent object.
   */
  public function __construct(
    public readonly FileUploadResult $result,
  ) {}

}
