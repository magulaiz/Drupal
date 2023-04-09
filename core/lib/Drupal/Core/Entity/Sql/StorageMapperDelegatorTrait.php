<?php

namespace Drupal\Core\Entity\Sql;

/**
 * Delegate ::mapColumnsOnLoad to the field item class.
 */
trait StorageMapperDelegatorTrait {

  /**
   * Get the field item class.
   *
   * @return string
   *   The field item class.
   */
  abstract protected function getFieldItemClass();

  /**
   * {@inheritDoc}
   */
  public function mapColumnsOnLoad(array $columns): ?array {
    $fieldItemClass = $this->getFieldItemClass();
    if (is_subclass_of($fieldItemClass, FieldItemStorageMapperInterface::class)) {
      return $fieldItemClass::mapColumnsOnLoad($columns);
    }
  }

}
