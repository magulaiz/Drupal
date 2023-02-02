<?php

namespace Drupal\image\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event to remove derivatives of a source image in all image styles.
 */
class FlushSourceImageEvent extends Event {

  /**
   * Constructs a FlushSourceImageEvent object.
   *
   * @param string $uri
   *   The URI of the source image.
   */
  public function __construct(public readonly string $uri) {
  }

}
