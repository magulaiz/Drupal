<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines views data for text fields.
 *
 * @ViewsFieldData(
 *   id = "text",
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   },
 *   argument = {
 *     "id" = "string",
 *   },
 *   filter = {
 *     "id" = "string",
 *   }
 * )
 */
class Text extends FieldViewsDataPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getViewsData(FieldStorageDefinitionInterface $field_storage, $column_name) {
    $views_field = parent::getViewsData($field_storage, $column_name);

    // Connect the text field to its formatter.
    if ($column_name == 'value') {
      $views_field['field']['format'] = $field_storage->getName() . '__format';
    }

    return $views_field;
  }

}
