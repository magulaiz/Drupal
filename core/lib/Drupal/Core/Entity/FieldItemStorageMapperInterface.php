<?php

namespace Drupal\Core\Entity;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Interface for field item classes that support storage mapping.
 */
interface FieldItemStorageMapperInterface extends FieldItemInterface {

  /**
   * Map columns on load.
   *
   * @param array $columns
   *   The columns to map.
   *
   * @return array|null
   *   The mapped field properties.
   */
  public static function mapColumnsOnLoad(array $columns): array;

  /**
   * Map columns on save.
   *
   * @param array $properties
   *   The field properties to map.
   *
   * @return array
   *   The mapped columns.
   */
  public static function mapColumnsOnSave(array $properties): array;

}
