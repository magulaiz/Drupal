<?php

namespace Drupal\Core\Entity\Sql;

trait StorageMapperDelegatorTrait {

  abstract protected function getFieldItemClass();

  public function mapColumnsOnLoad(array $columns): ?array {
    $fieldItemClass = $this->getFieldItemClass();
    if (is_subclass_of($fieldItemClass, FieldItemStorageMapperInterface::class)) {
      return $fieldItemClass::mapColumnsOnLoad($columns);
    }
  }

}
