<?php

declare(strict_types=1);

namespace com\example\PluginNamespace;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomPlugin extends Plugin {

  /**
   * Constructs a CustomPlugin attribute object.
   *
   * @param string $title
   *   The title.
   * @param string ...$base
   *   Plugin ID and deriver class.
   */
  public function __construct(
    public readonly string $title,
    ...$base
  ) {
    parent::__construct(...$base);
  }

}

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomPlugin2 extends Plugin {}
