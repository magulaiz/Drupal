<?php

namespace Drupal\views\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a Plugin attribute object for views display extender plugins.
 *
 * @see \Drupal\views\Plugin\views\display_extender\DisplayExtenderPluginBase
 *
 * @ingroup views_display_extender_plugins
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class ViewsDisplayExtender extends Plugin {

  /**
   * Constructs an ViewsDisplayExtender attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The plugin title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $short_title
   *   (optional) The short title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $help
   *   (optional) A short help string; this is displayed in the views UI.
   * @param bool $no_ui
   *   (optional) Whether the plugin should be not selectable in the UI.
   *   If set to TRUE, you can still use it via the API in config files.
   *   Defaults to FALSE.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public string $id,
    public ?TranslatableMarkup $title = NULL,
    public ?TranslatableMarkup $short_title = NULL,
    public ?TranslatableMarkup $help = NULL,
    public bool $no_ui = FALSE,
    public ?string $deriver = NULL
  ) {}

}
