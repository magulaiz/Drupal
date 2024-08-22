<?php

namespace Drupal\migrate;

/**
 * Provides an interface for the migrate source ID check.
 */
interface MigrateSourceIdCheckInterface {

  /**
   * Check whether the given source IDs are valid for the migration.
   *
   * @param array $source_ids
   *   The source IDs to check.
   *
   * @return bool
   *   True when the source IDs exist for the migration, else false.
   */
  public function hasSourceIds(array $source_ids) : bool;

}
