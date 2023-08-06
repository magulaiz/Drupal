<?php

namespace Drupal\Core\Database;

/**
 * This interface is a helper for queries creating tables on demand.
 *
 * An INSERT or MERGE query will automatically find if the caller implements
 * this interface and create the table on demand.
 */
interface SchemaProviderInterface {

  /**
   * A schema API array.
   *
   * @param string $table_name
   *   The name of the table being created. The implementation must check this.
   *
   * @return array|false
   *   A schema API array or FALSE if this implementation doesn't know about
   *   the $table_name table.
   *
   * @see hook_schema()
   * @see \Drupal\Core\Cache\DatabaseBackend
   */
  public function getSchema($table_name);

}
