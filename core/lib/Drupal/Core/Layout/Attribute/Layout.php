<?php

namespace Drupal\Core\Layout\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Layout\LayoutDefinition;

/**
 * Defines a Layout attribute object.
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
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Layout extends Plugin {

  /**
   * Constructs a Layout attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The human-readable name.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $category
   *   (optional) The human-readable category.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (optional) The description for advanced layouts.
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
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   * @param string $class
   *   The layout plugin class.
   * @param array $context_definitions
   *   The context definition.
   * @param array $config_dependencies
   *   The config dependencies.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public readonly ?TranslatableMarkup $category = NULL,
    public readonly ?TranslatableMarkup $description = NULL,
    public readonly ?string $template = NULL,
    public readonly string $theme_hook = 'layout',
    public readonly ?string $path = NULL,
    public readonly ?string $library = NULL,
    public readonly ?string $icon = NULL,
    public readonly ?string $icon_map = NULL,
    public readonly array $regions = [],
    public readonly ?string $default_region = NULL,
    public readonly ?string $deriver = NULL,
    public string $class = LayoutDefault::class,
    public readonly array $context_definitions = [],
    public readonly array $config_dependencies = [],
  ) {}

  /**
   * {@inheritdoc}
   */
  public function get(): LayoutDefinition {
    return new LayoutDefinition([
      'id' => $this->id,
      'label' => $this->label,
      'category' => $this->category,
      'description' => $this->description,
      'template' => $this->template,
      'theme_hook' => $this->theme_hook,
      'path' => $this->path,
      'library' => $this->library,
      'icon' => $this->icon,
      'icon_map' => $this->icon_map,
      'regions' => $this->regions,
      'default_region' => $this->default_region,
      'deriver' => $this->deriver,
      'class' => $this->class,
      'context_definitions' => $this->context_definitions,
      'config_dependencies' => $this->config_dependencies,
    ]);
  }

}
