<?php

namespace Drupal\Core\Entity\Sql;

/**
 * Interface for field item classes that support storage mapping.
 */
interface StorageColumnStaticMapperInterface {

  /**
   * Map columns on load.
   *
   * @param array $columns
   *   The columns to map.
   *
   * @return array|null
   *   The mapped columns, or NULL to fall back to default mapping.
   */
  public static function mapColumnsOnLoad(array $columns): ?array;

  /**
   * Map columns on save.
   *
   * @param array $columns
   *   The columns to map.
   *
   * @return array|null
   *   The mapped columns, or NULL to fall back to default mapping.
   */
  public static function mapColumnsOnSave(array $columns): ?array;

}
