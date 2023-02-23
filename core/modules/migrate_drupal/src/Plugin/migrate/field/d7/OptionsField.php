<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d7;

use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

/**
 * @MigrateField(
 *   id = "options",
 *   core = {7},
 *   type_map = {
 *     "options" = "options",
 *   },
 *   source_module = "options",
 *   destination_module = "options"
 * )
 */
class OptionsField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      'options' => 'options_default',
    ];
  }

}
