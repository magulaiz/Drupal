<?php

namespace Drupal\Core\Entity\Sql;

trait StorageColumnMapperDelegatorTrait {

  abstract protected function getFieldItemClass();

  public function mapColumnsOnLoad(array $columns): ?array {
    $fieldItemClass = $this->getFieldItemClass();
    if (is_subclass_of($fieldItemClass, StorageColumnStaticMapperInterface::class)) {
      return $fieldItemClass::mapColumnsOnLoad($columns);
    }
  }

  public function mapColumnsOnSave(array $columns): ?array {
    $fieldItemClass = $this->getFieldItemClass();
    if (is_subclass_of($fieldItemClass, StorageColumnStaticMapperInterface::class)) {
      return $fieldItemClass::mapColumnsOnSave($columns);
    }
  }

}
