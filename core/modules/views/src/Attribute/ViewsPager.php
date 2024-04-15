<?php

namespace Drupal\views\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a views pager plugins type attribute for plugin discovery.
 *
 * @see \Drupal\views\Plugin\views\pager\PagerPluginBase
 *
 * @ingroup views_pager_plugins
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class ViewsPager extends Plugin {

  /**
   * Constructs a ViewsPager attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   The plugin title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $short_title
   *   (optional) The short title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $help
   *   (optional) A short help string; this is displayed in the views UI.
   * @param string|null $theme
   *   (optional) The theme function used to render the pager's output.
   * @param string[]|null $display_types
   *   (optional) The types of the display this plugin can be used with.
   *   For example the Feed display defines the type 'feed', so only rss style
   *   and row plugins can be used in the views UI.
   * @param string[] $base
   *   (optional) The base tables on which this access plugin can be used.
   *   If no base table is specified the plugin can be used with all tables.
   * @param bool $no_ui
   *   (optional) Whether the plugin should be not selectable in the UI.
   *   If set to TRUE, you can still use it via the API in config files.
   *   Defaults to FALSE.
   * @param bool $register_theme
   *   (optional) Whether or not to register a theme function automatically.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public string $id,
    public TranslatableMarkup $title,
    public ?TranslatableMarkup $short_title = NULL,
    public ?TranslatableMarkup $help = NULL,
    public ?string $theme = NULL,
    public ?array $display_types = NULL,
    public array $base = [],
    public bool $no_ui = FALSE,
    public bool $register_theme = TRUE,
    public ?string $deriver = NULL
  ) {}

}
