<?php

namespace Drupal\views\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a ViewsArgumentValidator attribute object for plugin discovery.
 *
 * Plugin Namespace: Plugin\ViewsArgumentValidator
 *
 * @see \Drupal\views\Plugin\views\argument_validator\ArgumentValidatorPluginBase
 *
 * @ingroup views_argument_validate_plugins
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ViewsArgumentValidator extends Plugin {
  /**
   * Constructs a ViewsArgumentValidator attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param string|null $deriver
   *   (optional) The deriver class.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The plugin title used in the views UI.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $short_title
   *   (optional) The short title used in the views UI.
   * @param string $entity_type
   *   (optional) Entity type.
   * @param bool $no_ui
   *   Whether the plugin should be not selectable in the UI.
   *   If set to TRUE, you can still use it via the API in config files.
   */
  public function __construct(
    public readonly string $id,
    public readonly ?string $deriver = NULL,
    public readonly ?TranslatableMarkup $title = NULL,
    public readonly ?TranslatableMarkup $short_title = NULL,
    public readonly ?string $entity_type,
    public readonly ?bool $no_ui = NULL,
  ) {}

}
