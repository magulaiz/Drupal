<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d6;

use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

// cspell:ignore optionwidgets

/**
 * @MigrateField(
 *   id = "optionwidgets",
 *   core = {6},
 *   type_map = {
 *     "optionwidgets" = "optionwidgets"
 *   },
 *   source_module = "optionwidgets",
 *   destination_module = "options"
 * )
 */
class OptionWidgetsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      'optionwidgets' => 'options_default',
    ];
  }

}
