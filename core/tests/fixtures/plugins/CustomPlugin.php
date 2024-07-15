<?php

declare(strict_types=1);

namespace com\example\PluginNamespace;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Component\Plugin\Attribute\PluginExtender;
use Drupal\Component\Plugin\Attribute\PluginDeprecatedProperty;

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomPlugin extends Plugin {

  /**
   * Constructs a CustomPlugin attribute object.
   *
   * @param string $id
   *   The attribute class ID.
   * @param string $title
   *   The title.
   */
  public function __construct(
    public readonly string $id,
    public readonly string $title,
    #[PluginDeprecatedProperty]
    public readonly string $third_party_property = 'default',
  ) {}

}

/**
 * Custom plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomPlugin2 extends Plugin {}

#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomPluginThirdParty extends PluginExtender {
  public function __construct(
    // This will be allowed to override the property with the same name on
    // CustomPlugin, because on that attribute class the property is marked as
    // deprecated.
    public readonly string $third_party_property,
  ) {

  }

}
