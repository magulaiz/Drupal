<?php

namespace Drupal\editor\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an Editor attribute object.
 *
 * Plugin Namespace: Plugin\Editor
 *
 * For a working example, see \Drupal\ckeditor5\Plugin\Editor\CKEditor5
 *
 * @see \Drupal\editor\Plugin\EditorPluginInterface
 * @see \Drupal\editor\Plugin\EditorBase
 * @see \Drupal\editor\Plugin\EditorManager
 * @see hook_editor_info_alter()
 * @see plugin_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Editor extends Plugin {

  /**
   * Constructs an Editor object.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The human-readable name of the text editor, translated
   * @param bool $supports_content_filtering
   *   Whether the editor supports "allowed content only" filtering.
   * @param bool $supports_inline_editing
   *   Whether the editor supports the inline editing provided by the Edit
   *   module.
   * @param bool $is_xss_safe
   *   Whether this text editor is not vulnerable to XSS attacks.
   * @param string[] $supported_element_types
   *   On which form element #types this text editor is capable of working.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public string $id,
    public TranslatableMarkup $label,
    public bool $supports_content_filtering,
    public bool $supports_inline_editing,
    public bool $is_xss_safe,
    public array $supported_element_types,
    public ?string $deriver = NULL,
  ) {}

}
