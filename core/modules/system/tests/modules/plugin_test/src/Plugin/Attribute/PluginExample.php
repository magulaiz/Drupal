<?php

namespace Drupal\plugin_test\Plugin\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines a custom PluginExample attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PluginExample extends Plugin {

  /**
   * Constructs a PluginExample attribute.
   *
   * @param string $custom
   *   Some other sample plugin metadata.
   * @param string ...$base
   *   Plugin ID and deriver class.
   */
  public function __construct(
    public readonly string $id,
    public readonly ?string $custom = NULL
  ) {}

}
