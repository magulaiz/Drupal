<?php

namespace Drupal\Core\Field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Interface definition for EntityReference items.
 */
interface EntityReferenceItemInterface {

  /**
   * Returns the referenceable entity types and bundles.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition for which to retrieve the referenceable entity
   *   types and bundles.
   *
   * @return array
   *   An array keyed by entity type IDs where the values are an indexed
   *   array of bundle IDs or entity type ID if the entity type does not have
   *   bundles.
   */
  public static function getReferenceableBundles(FieldDefinitionInterface $field_definition);

}
