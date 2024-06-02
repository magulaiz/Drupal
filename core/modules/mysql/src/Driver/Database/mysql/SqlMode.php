<?php

namespace Drupal\mysql\Driver\Database\mysql;

/**
 * @addtogroup database
 * @{
 */

/**
 * SQL Mode Settings that are relevant to Drupal's operation.
 * 
 * @link https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html
 * @link https://mariadb.com/kb/en/sql-mode/
 */
class SqlMode {
  /**
   * ANSI changes the SQL syntax to be closer to ANSI SQL.
   * 
   * This is a meta-mode that sets a certain set of other SQL modes.
   */
  const ANSI = "ANSI";

  const TRADITIONAL = "TRADITIONAL";
}

/**
 * @} End of "addtogroup database".
 */
