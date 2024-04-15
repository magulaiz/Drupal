<?php

namespace Drupal\plugin_test\Plugin\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines a custom PluginExample attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class PluginExample extends Plugin {

  /**
   * Constructs a PluginExample attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param string $custom
   *   Some other sample plugin metadata.
   */
  public function __construct(
    public string $id,
    public ?string $custom = NULL
  ) {}

}
