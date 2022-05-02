<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines views data for fields of type "text_long".
 *
 * @ViewsFieldData(
 *   id = "text_long",
 *   argument = {
 *     "id" = "string",
 *   },
 *   filter = {
 *     "id" = "string",
 *   }
 * )
 */
class TextLong extends FieldViewsDataPluginBase {

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
