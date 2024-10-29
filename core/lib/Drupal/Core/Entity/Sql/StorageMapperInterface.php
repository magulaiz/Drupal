<?php

namespace Drupal\Core\Entity\Sql;

/**
 * Interface for field storage definitions that support storage mapping.
 */
interface StorageMapperInterface {

  /**
   * Maps columns on load.
   *
   * @param array $columns
   *   The columns to map.
   *
   * @return array|null
   *   The mapped columns, or NULL to fall back to default mapping.
   */
  public function mapColumnsOnLoad(array $columns): ?array;

  /**
   * Maps columns on save.
   *
   * @param array $columns
   *   The columns to map.
   *
   * @return array|null
   *   The mapped columns, or NULL to fall back to default mapping.
   */
  public function mapColumnsOnSave(array $columns): ?array;

}
