<?php

namespace Drupal\media\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\media\MediaInterface;

/**
 * Base implementation of entity browser events.
 */
class MediaBuildEmbedEvent extends Event {

  protected string $viewMode;
  protected MediaInterface $media;
  protected array $build;
  protected \DOMElement $node;

  /**
   * Constructs a EntitySelectionEvent object.
   *
   * @param string $entity_browser_id
   *   Entity browser ID.
   * @param string $instance_uuid
   *   Entity browser instance UUID.
   */
  public function __construct(string $viewMode, MediaInterface $media, array $build, \DOMElement $node) {
    $this->viewMode = $viewMode;
    $this->media = $media;
    $this->build = $build;
    $this->node = $node;
  }

  /**
   * @return string
   */
  public function getViewMode(): string {
    return $this->viewMode;
  }


  /**
   * @return \Drupal\media\MediaInterface
   */
  public function getMedia(): MediaInterface {
    return $this->media;
  }

  /**
   * @return array
   */
  public function getBuild(): array {
    return $this->build;
  }

  /**
   * @param array $build
   */
  public function setBuild(array $build): void {
    $this->build = $build;
  }

  /**
   * @return \DOMElement
   */
  public function getNode(): \DOMElement {
    return $this->node;
  }

}
