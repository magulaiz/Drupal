<?php

namespace Drupal\Core\Field;

/**
 * Interface for entity reference lists of field items.
 */
interface EntityReferenceFieldItemListInterface extends FieldItemListInterface {

  /**
   * Gets the IDS of entities referenced by this field, preserving field item deltas.
   *
   * @return int|string[]
   *   An array of entity IDs keyed by field item deltas.
   */
  public function referencedIds(): array;

  /**
   * Gets the entities referenced by this field, preserving field item deltas.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entity objects keyed by field item deltas.
   */
  public function referencedEntities();

}
