<?php

namespace Drupal\image\Event\ImageDerivative;

use Drupal\image\ImageProcessPipelineInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Provides a class for events related to processing image derivatives.
 */
abstract class BaseEvent extends GenericEvent {

  /**
   * Returns the ImageProcessPipeline object subject of the event.
   *
   * @return \Drupal\image\ImageProcessPipelineInterface
   *   The ImageProcessPipeline object.
   */
  public function getPipeline(): ImageProcessPipelineInterface {
    return $this->getSubject();
  }

}
