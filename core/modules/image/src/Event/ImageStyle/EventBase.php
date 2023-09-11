<?php

namespace Drupal\image\Event\ImageStyle;

use Drupal\image\ImageStyleInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Provides a class for events related to image styles.
 */
abstract class EventBase extends GenericEvent {

  /**
   * Returns the Image Style object subject of the event.
   *
   * @return \Drupal\image\ImageStyleInterface
   *   The Image Style object.
   */
  public function getImageStyle(): ImageStyleInterface {
    return $this->getSubject();
  }

}
