<?php

declare(strict_types=1);

namespace com\example\PluginNamespace;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class CustomPlugin extends Plugin {

  /**
   * Constructs a CustomPlugin attribute object.
   *
   * @param string $id
   *   The attribute class ID.
   * @param string $title
   *   The title.
   */
  public function __construct(
    public string $id,
    public string $title
  ) {}

}

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class CustomPlugin2 extends Plugin {}
