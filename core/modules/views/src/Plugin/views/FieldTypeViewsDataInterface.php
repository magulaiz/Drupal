<?php

namespace Drupal\views\Plugin\views;

use Drupal\Core\Field\FieldStorageDefinitionInterface;

interface FieldTypeViewsDataInterface {

  /**
   * Returns the data that views uses to display the field.
   *
   * @return array
   *   The data views uses to display the field.
   */
  public function getViewsData(FieldStorageDefinitionInterface $field_storage, $column_name);

}
