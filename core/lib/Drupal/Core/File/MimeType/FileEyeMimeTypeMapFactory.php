<?php

namespace Drupal\Core\File\MimeType;

use FileEye\MimeMap\Map\MimeMapInterface;
use FileEye\MimeMap\MapHandler;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * A factory for creating instances of the MIME type map using FileEye.
 */
class FileEyeMimeTypeMapFactory {

  public function __construct(
    protected readonly EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Creates an instance of the MIME type map.
   *
   * @return \Drupal\Core\File\MimeType\MimeTypeMapInterface
   *   The MIME type map.
   */
  public function create(): MimeTypeMapInterface {
    MapHandler::setDefaultMapClass(FileEyeMimeTypeMap::class);
    $fileEyeMap = FileEyeMimeTypeMap::getInstance();
    assert($fileEyeMap instanceof MimeMapInterface);
    $map = new FileEyeMimeTypeMapBridge($fileEyeMap);
    $this->eventDispatcher->dispatch(new MimeTypeMapLoadedEvent($map));

    return $map;
  }

}
