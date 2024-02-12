<?php

namespace Drupal\mongodb\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem as CoreBooleanItem;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Overriding the fieldtype plugin "boolean".
 */
class BooleanItem extends CoreBooleanItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'bool',
        ],
      ],
    ];
  }

}
