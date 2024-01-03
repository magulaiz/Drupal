<?php

declare(strict_types=1);

namespace Drupal\entity_test\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\MapItem;

/**
 * Field type with JSON data storage.
 *
 * @FieldType(
 *   id = "json_backed_test",
 *   label = @Translation("JSON-backed field (test)"),
 *   description = @Translation("A field containing JSON-backed properties."),
 * )
 */
class JsonBackedPropertiesItem extends MapItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'json_value' => [
          'type' => 'json',
        ],
      ],
    ];
  }

}
