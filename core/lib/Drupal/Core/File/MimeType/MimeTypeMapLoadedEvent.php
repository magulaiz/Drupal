<?php

namespace Drupal\Core\File\MimeType;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when the MIME type map is loaded.
 */
class MimeTypeMapLoadedEvent extends Event {

  public function __construct(
    public readonly MimeTypeMapInterface $map,
  ) {}

}
