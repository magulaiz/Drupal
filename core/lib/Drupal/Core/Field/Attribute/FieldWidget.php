<?php

namespace Drupal\Core\Field\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a FieldWidget attribute for plugin discovery.
 *
 * Plugin Namespace: Plugin\Field\FieldWidget
 *
 * Widgets handle how fields are displayed in edit forms.
 *
 * Additional attribute keys for widgets can be defined in
 * hook_field_widget_info_alter().
 *
 * @see \Drupal\Core\Field\WidgetPluginManager
 * @see \Drupal\Core\Field\WidgetInterface
 *
 * @ingroup field_widget
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class FieldWidget extends Plugin {

  /**
   * Constructs a FieldWidget attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $label
   *   (optional) The human-readable name of the widget type.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   (optional) A short description of the widget type.
   * @param string[] $field_types
   *   (optional) An array of field types the widget supports.
   * @param bool $multiple_values
   *   (optional) Does the field widget handles multiple values at once.
   * @param int|null $weight
   *   (optional) An integer to determine weight of this widget relative to
   *   other widgets. Other widgets are in the Field UI when selecting a widget
   *   for a given field.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public string $id,
    public ?TranslatableMarkup $label = NULL,
    public ?TranslatableMarkup $description = NULL,
    public array $field_types = [],
    public bool $multiple_values = FALSE,
    public ?int $weight = NULL,
    public ?string $deriver = NULL,
  ) {}

}
