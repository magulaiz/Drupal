<?php

declare(strict_types=1);

namespace Drupal\views\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a Plugin attribute object for views exposed form plugins.
 *
 * @see \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginInterface
 * @see \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginBase
 *
 * @ingroup views_exposed_form_plugins
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class ViewsExposedForm extends Plugin {

  /**
   * Constructs a views exposed form attribute object.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   The plugin title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $short_title
   *   (optional) The short title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $help
   *   (optional) A short help string; this is displayed in the views UI.
   * @param string[]|null $display_types
   *   (optional) The types of the display this plugin can be used with.
   *   For example the Feed display defines the type 'feed', so only rss style
   *   and row plugins can be used in the views UI.
   * @param string[] $base
   *   (optional) The base tables on which this exposed form plugin can be used.
   *   If no base table is specified the plugin can be used with all tables.
   * @param bool $no_ui
   *   (optional) Whether the plugin should be not selectable in the UI.
   *   If it's set to TRUE, you can still use it via the API in config files.
   *   Defaults to FALSE.
   * @param bool $register_theme
   *   (optional) Whether to register a theme function automatically. Defaults
   *   to TRUE.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public string $id,
    public TranslatableMarkup $title,
    public ?TranslatableMarkup $short_title = NULL,
    public ?TranslatableMarkup $help = NULL,
    public ?array $display_types = NULL,
    public array $base = [],
    public bool $no_ui = FALSE,
    public bool $register_theme = TRUE,
    public ?string $deriver = NULL
  ) {}

}
