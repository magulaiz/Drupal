<?php

declare(strict_types=1);

namespace Drupal\mysqli\Driver\Database\mysqli;

use Drupal\Core\Database\Connection as BaseConnection;
use Drupal\Core\Database\ConnectionNotDefinedException;
use Drupal\Core\Database\DatabaseAccessDeniedException;
use Drupal\Core\Database\DatabaseNotFoundException;
use Drupal\Core\Database\Transaction\TransactionManagerInterface;
use Drupal\mysql\Driver\Database\mysql\Connection as BaseMySqlConnection;

/**
 * MySQLi implementation of \Drupal\Core\Database\Connection.
 */
class Connection extends BaseMySqlConnection {

  /**
   * {@inheritdoc}
   */
  protected $statementWrapperClass = Statement::class;

  /**
   * Stores the server version after it has been retrieved from the database.
   */
  private string $serverVersion;

  public function __construct(
    \mysqli $connection,
    array $connectionOptions = [],
  ) {
    // If the SQL mode doesn't include 'ANSI_QUOTES' (explicitly or via a
    // combination mode), then MySQL doesn't interpret a double quote as an
    // identifier quote, in which case use the non-ANSI-standard backtick.
    //
    // @see https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html#sqlmode_ansi_quotes
    $ansiQuotesModes = ['ANSI_QUOTES', 'ANSI'];
    $isAnsiQuotesMode = FALSE;
    if (isset($connectionOptions['init_commands']['sql_mode'])) {
      foreach ($ansiQuotesModes as $mode) {
        // None of the modes in $ansiQuotesModes are substrings of other modes
        // that are not in $ansiQuotesModes, so a simple stripos() does not
        // return false positives.
        if (stripos($connectionOptions['init_commands']['sql_mode'], $mode) !== FALSE) {
          $isAnsiQuotesMode = TRUE;
          break;
        }
      }
    }

    if ($this->identifierQuotes === ['"', '"'] && !$isAnsiQuotesMode) {
      $this->identifierQuotes = ['`', '`'];
    }

    BaseConnection::__construct($connection, $connectionOptions);
  }

  /**
   * {@inheritdoc}
   */
  public static function open(array &$connectionOptions = []) {
    // Sets mysqli error reporting mode to report errors from mysqli function
    // calls and to throw mysqli_sql_exception for errors.
    // @see https://www.php.net/manual/en/mysqli-driver.report-mode.php
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if (isset($connectionOptions['_dsn_utf8_fallback']) && $connectionOptions['_dsn_utf8_fallback'] === TRUE) {
      // Only used during the installer version check, as a fallback from utf8mb4.
      $charset = 'utf8';
    }
    else {
      $charset = 'utf8mb4';
    }

    // Allow PDO options to be overridden.
    $connectionOptions += [
      'pdo' => [],
    ];

    try {
      $mysqli = @new \mysqli(
        $connectionOptions['host'],
        $connectionOptions['username'],
        $connectionOptions['password'],
        $connectionOptions['database'] ?? '',
        !empty($connectionOptions['port']) ? (int) $connectionOptions['port'] : 3306,
        $connectionOptions['unix_socket'] ?? ''
      );
      if (!$mysqli->set_charset($charset)) {
        throw new InvalidCharsetException('Invalid charset ' . $charset);
      }
    }
    catch (\mysqli_sql_exception $e) {
      if ($e->getCode() === static::DATABASE_NOT_FOUND) {
        throw new DatabaseNotFoundException($e->getMessage(), $e->getCode(), $e);
      }
      elseif ($e->getCode() === static::ACCESS_DENIED) {
        throw new DatabaseAccessDeniedException($e->getMessage(), $e->getCode(), $e);
      }
      else {
        throw new ConnectionNotDefinedException('Invalid database connection: ' . $e->getMessage(), $e->getCode(), $e);
      }
      throw $e;
    }

    // Force MySQL to use the UTF-8 character set. Also set the collation, if a
    // certain one has been set; otherwise, MySQL defaults to
    // 'utf8mb4_general_ci' (MySQL 5) or 'utf8mb4_0900_ai_ci' (MySQL 8) for
    // utf8mb4.
    if (!empty($connectionOptions['collation'])) {
      $mysqli->query('SET NAMES ' . $charset . ' COLLATE ' . $connectionOptions['collation']);
    }
    else {
      $mysqli->query('SET NAMES ' . $charset);
    }

    // Set MySQL init_commands if not already defined.  Default Drupal's MySQL
    // behavior to conform more closely to SQL standards.  This allows Drupal
    // to run almost seamlessly on many different kinds of database systems.
    // These settings force MySQL to behave the same as postgresql, or sqlite
    // in regards to syntax interpretation and invalid data handling.  See
    // https://www.drupal.org/node/344575 for further discussion. Also, as MySQL
    // 5.5 changed the meaning of TRADITIONAL we need to spell out the modes one
    // by one.
    $connectionOptions += [
      'init_commands' => [],
    ];

    $connectionOptions['init_commands'] += [
      'sql_mode' => "SET sql_mode = 'ANSI,TRADITIONAL'",
    ];
    if (!empty($connectionOptions['isolation_level'])) {
      $connectionOptions['init_commands'] += [
        'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL ' . strtoupper($connectionOptions['isolation_level']),
      ];
    }

    // Execute initial commands.
    foreach ($connectionOptions['init_commands'] as $sql) {
      $mysqli->query($sql);
    }

    return $mysqli;
  }

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return 'mysqli';
  }

  /**
   * {@inheritdoc}
   */
  public function clientVersion() {
    return \mysqli_get_client_info();
  }

  /**
   * {@inheritdoc}
   */
  public function quote($string, $parameter_type = \PDO::PARAM_STR) {
    return "'" . $this->connection->escape_string((string) $string) . "'";
  }

  /**
   * {@inheritdoc}
   */
  public function lastInsertId(?string $name = NULL): string {
    return (string) $this->connection->insert_id;
  }

  /**
   * {@inheritdoc}
   */
  public function exceptionHandler() {
    return new ExceptionHandler();
  }

  /**
   * {@inheritdoc}
   */
  protected function driverTransactionManager(): TransactionManagerInterface {
    return new TransactionManager($this);
  }

}
