<?php

namespace Drupal\media\Event;

use Drupal\media\MediaInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before a media entity is rendered to be embedded.
 *
 * Subscribers can alter the media entity passed to this event to customize the
 * way the embed entity is rendered. This is safe as the entity is a clone and
 * the original media entity will not be changed. For instance, one subscriber
 * can read a 'data-*' attribute from the $node element and apply certain
 * changes to $media, allowing a per-embed instance customization.
 *
 * @see \Drupal\media\Plugin\Filter\MediaEmbed::process()
 * @see \Drupal\media\Plugin\Filter\MediaEmbed::applyPerEmbedMediaOverrides()
 */
class MediaPreEmbedEvent extends Event {

  /**
   * Constructs a new even instance.
   *
   * @param \DOMElement $node
   *   The DOM element node.
   * @param \Drupal\media\MediaInterface $media
   *   The embed media entity.
   */
  public function __construct(
    protected \DOMElement $node,
    protected MediaInterface $media
  ) {
  }

  /**
   * Returns the DOM element node.
   *
   * @return \DOMElement
   *   The DOM element node.
   */
  public function getNode(): \DOMElement {
    return $this->node;
  }

  /**
   * Returns the embed media entity.
   *
   * @return \Drupal\media\MediaInterface
   *   The embed media entity.
   */
  public function getMedia(): MediaInterface {
    return $this->media;
  }

}
