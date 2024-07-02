<?php

namespace Drupal\field_test\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'entity_reference_with_text' entity field item.
 *
 * @FieldType(
 *   id = "entity_reference_with_text",
 *   label = @Translation("Test entity reference field"),
 *   description = @Translation("Dummy field type used for tests."),
 * )
 */
class EntityReferenceWithTextItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);
    $properties['test'] = DataDefinition::create('string');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    $schema['columns']['test']['type'] = 'text';
    return $schema;
  }

}
