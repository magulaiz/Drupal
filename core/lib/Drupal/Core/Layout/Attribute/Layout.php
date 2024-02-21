<?php

namespace Drupal\Core\Layout\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Layout\LayoutDefinition;

/**
 * Defines an Layout attribute object.
 *
 * Layouts are used to define a list of regions and then output render arrays
 * in each of the regions, usually using a template.
 *
 * Plugin Namespace: Plugin\Layout
 *
 * @see \Drupal\Core\Layout\LayoutInterface
 * @see \Drupal\Core\Layout\LayoutDefault
 * @see \Drupal\Core\Layout\LayoutPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Layout extends Plugin {

  /**
   * Constructs an Action attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $label
   *   The human-readable name.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (optional) The description for advanced layouts.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $category
   *   The human-readable category.
   * @param string|null $template
   *   (optional) The template file to render the layout.
   * @param string $theme_hook
   *   (optional) The template hook to render the layout.
   * @param string|null $path
   *   (optional) Path (relative to the module or theme) to resources like icon or template.
   * @param string|null $library
   *   (optional) The asset library.
   * @param string|null $icon
   *   (optional) The path to the preview image (relative to the 'path' given).
   * @param string[][]|null $icon_map
   *   (optional) The icon map.
   * @param array $regions
   *   An associative array of regions in this layout.
   * @param string|null $default_region
   *   The default region.
   * @param string $class
   *   The layout plugin class.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label = NULL,
    public readonly ?TranslatableMarkup $description = NULL,
    public readonly TranslatableMarkup $category = NULL,
    public readonly ?string $template = NULL,
    public readonly string $theme_hook = 'layout',
    public readonly ?string $path = NULL,
    public readonly ?string $library = NULL,
    public readonly ?string $icon = NULL,
    public readonly ?string $icon_map = NULL,
    public readonly array $regions = [],
    public readonly string $default_region = NULL,
    public readonly string $class = LayoutDefault::class,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function get() {
    return new LayoutDefinition($this->definition);
  }

}
