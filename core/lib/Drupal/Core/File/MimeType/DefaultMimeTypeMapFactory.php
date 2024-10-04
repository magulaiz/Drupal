<?php

namespace Drupal\Core\File\MimeType;

/**
 * Factory for creating the default MIME type map.
 */
class DefaultMimeTypeMapFactory {

  /**
   * The MIME map modifiers.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeMapModifierInterface[]
   */
  protected array $mapModifiers = [];

  /**
   * Creates an instance of the MIME type map.
   *
   * @return \Drupal\Core\File\MimeType\MimeTypeMapInterface
   *   The MIME type map.
   */
  public function create(): MimeTypeMapInterface {
    $map = new DefaultMimeTypeMap();
    $map->loadDefault();
    foreach ($this->mapModifiers as $modifier) {
      $modifier->modifyMimeTypeMap($map);
    }

    return $map;
  }

  /**
   * Adds a MIME map modifier.
   *
   * This is typically called on container build.
   */
  public function addModifier(MimeTypeMapModifierInterface $modifier): void {
    $this->mapModifiers[] = $modifier;
  }

}
