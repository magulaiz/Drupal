<?php

declare(strict_types=1);

namespace Drupal\Core\File\MimeType;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Factory for creating the default MIME type map.
 */
class DefaultMimeTypeMapFactory {

  public function __construct(
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Creates an instance of the MIME type map.
   *
   * @return \Drupal\Core\File\MimeType\MimeTypeMapInterface
   *   The MIME type map.
   */
  public function create(): MimeTypeMapInterface {
    $map = new DefaultMimeTypeMap();
    $map->loadDefault();
    $this->eventDispatcher->dispatch(new MimeTypeMapLoadedEvent($map));
    return $map;
  }

}
