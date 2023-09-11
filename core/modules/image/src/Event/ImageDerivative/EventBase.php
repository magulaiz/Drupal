<?php

namespace Drupal\image\Event\ImageDerivative;

use Drupal\image\ImageProcessorPipelineInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Provides a class for events related to processing image derivatives.
 */
abstract class EventBase extends GenericEvent {

  /**
   * Returns the ImageProcessorPipeline object subject of the event.
   *
   * @return \Drupal\image\ImageProcessorPipelineInterface
   *   The ImageProcessorPipeline object.
   */
  public function getPipeline(): ImageProcessorPipelineInterface {
    return $this->getSubject();
  }

}
