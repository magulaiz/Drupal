<?php

namespace Drupal\mongodb\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\ChangedItem as CoreChangedItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Overriding the fieldtype plugin "changed".
 */
class ChangedItem extends CoreChangedItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'date',
        ],
      ],
    ];
  }

}
