<?php

declare(strict_types=1);

namespace Drupal\views\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines an abstract base class for all views plugin attributes.
 */
abstract class ViewsPluginAttributeBase extends Plugin {

  /**
   * Constructs a views plugin attribute base object.
   *
   * @param string $id
   *   The attribute class ID.
   * @param bool $register_theme
   *   (optional) Whether or not to register a theme function automatically.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public readonly string $id,
    public readonly bool $register_theme = TRUE,
    public readonly ?string $deriver = NULL
  ) {}

}
