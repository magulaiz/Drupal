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
   * This is a meta-mode that sets a certain set of other SQL modes
   * When selected. Drupal enables ANSI mode by default. The
   * following modes are set when ANSI is selected:
   *
   *   ANSI_QUOTES
   *   IGNORE_SPACE
   *   ONLY_FULL_GROUP_BY (Included by Mysql, but not Mariadb.)
   *   PIPES_AS_CONCAT
   *   REAL_AS_FLOAT
   */
  const ANSI = "ANSI";

  /**
   * TRADITIONAL makes MySQL behave like a “traditional” SQL database system.
   *
   * This is a meta-mode that sets a certain set of other SQL modes
   * when selected. Drupal enables TRADITIONAL mode by default. The
   * following modes are set when TRADITIONAL is selected:
   *
   *   ERROR_FOR_DIVISION_BY_ZERO
   *   NO_ENGINE_SUBSTITUTION
   *   NO_ZERO_DATE
   *   NO_ZERO_IN_DATE
   *   STRICT_ALL_TABLES
   *   STRICT_TRANS_TABLES
   */
  const TRADITIONAL = "TRADITIONAL";

}

/**
 * @} End of "addtogroup database".
 */
