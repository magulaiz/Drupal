<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Event\DatabaseEvent;

/**
 * Interface for database connections.
 *
 * This interface implements an API for routine use of a database connection,
 * but not all the methods for manipulating the underlying database, e.g. for
 * schema changes.
 *
 * @see \Drupal\Core\Database\Connection
 * @see \Drupal\Core\Database\NonTransactionalConnection
 */
interface DatabaseConnectionInterface {

  /**
   * Returns the name of the database engine accessed by this driver.
   *
   * @return string
   *   The database engine name.
   */
  public function databaseType();

  /**
   * Get the module name of the module that is providing the database driver.
   *
   * @return string
   *   The module name of the module that is providing the database driver, or
   *   "core" when the driver is not provided as part of a module.
   */
  public function getProvider(): string;

  /**
   * Runs a simple query to validate json datatype support.
   *
   * @return bool
   *   Returns the query result.
   */
  public function hasJson(): bool;

  /**
   * Returns the type of database driver.
   *
   * This is not necessarily the same as the type of the database itself. For
   * instance, there could be two MySQL drivers, mysql and mysqlMock. This
   * function would return different values for each, but both would return
   * "mysql" for databaseType().
   *
   * @return string
   *   The type of database driver.
   */
  public function driver();

  /**
   * Returns the version of the database server.
   *
   * Assumes the client connection is \PDO. Non-PDO based drivers need to
   * override this method.
   *
   * @return string
   *   The version of the database server.
   */
  public function version();

  /**
   * Returns the version of the database client.
   *
   * Assumes the client connection is \PDO. Non-PDO based drivers need to
   * override this method.
   *
   * @return string
   *   The version of the database client.
   */
  public function clientVersion();

  /**
   * Returns the key this connection is associated with.
   *
   * @return string|null
   *   The key of this connection, or NULL if no key is set.
   */
  public function getKey();

  /**
   * Returns the target this connection is associated with.
   *
   * @return string|null
   *   The target string of this connection, or NULL if no target is set.
   */
  public function getTarget();

  /**
   * Determines if this driver supports transactional DDL.
   *
   * DDL queries are those that change the schema, such as ALTER queries.
   *
   * @return bool
   *   TRUE if this connection supports transactions for DDL queries, FALSE
   *   otherwise.
   */
  public function supportsTransactionalDDL();

  /**
   * Gets the current logging object for this connection.
   *
   * @return \Drupal\Core\Database\Log|null
   *   The current logging object for this connection. If there isn't one,
   *   NULL is returned.
   */
  public function getLogger();

  /**
   * Associates a logging object with this connection.
   *
   * @param \Drupal\Core\Database\Log $logger
   *   The logging object we want to use.
   */
  public function setLogger(Log $logger);

  /**
   * Returns the prefix of the tables.
   *
   * @return string
   *   The table prefix.
   */
  public function getPrefix(): string;

  /**
   * Executes a query string against the database.
   *
   * This method provides a central handler for the actual execution of every
   * query. All queries executed by Drupal are executed as prepared statements.
   *
   * @param string $query
   *   The query to execute. This is a string containing an SQL query with
   *   placeholders.
   * @param array $args
   *   The associative array of arguments for the prepared statement.
   * @param array $options
   *   An associative array of options to control how the query is run. The
   *   given options will be merged with self::defaultOptions(). See the
   *   documentation for self::defaultOptions() for details.
   *   Typically, $options['return'] will be set by a default or by a query
   *   builder, and should not be set by a user.
   *
   * @return \Drupal\Core\Database\StatementInterface|null
   *   The executed statement.
   *
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   * @throws \Drupal\Core\Database\IntegrityConstraintViolationException
   * @throws \InvalidArgumentException
   *
   * @see \Drupal\Core\Database\Connection::defaultOptions()
   */
  public function query($query, array $args = [], $options = []);

  /**
   * Runs a limited-range query on this database object.
   *
   * Use this as a substitute for ->query() when a subset of the query is to be
   * returned. User-supplied arguments to the query should be passed in as
   * separate parameters so that they can be properly escaped to avoid SQL
   * injection attacks.
   *
   * @param string $query
   *   A string containing an SQL query.
   * @param int $from
   *   The first result row to return.
   * @param int $count
   *   The maximum number of result rows to return.
   * @param array $args
   *   (optional) An array of values to substitute into the query at placeholder
   *    markers.
   * @param array $options
   *   (optional) An array of options on the query.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   A database query result resource, or NULL if the query was not executed
   *   correctly.
   */
  public function queryRange($query, $from, $count, array $args = [], array $options = []);

  /**
   * Prepares and returns a SELECT query object.
   *
   * @param string|\Drupal\Core\Database\Query\SelectInterface $table
   *   The base table name or subquery for this query, used in the FROM clause.
   *   If a string, the table specified will also be used as the "base" table
   *   for query_alter hook implementations.
   * @param string $alias
   *   (optional) The alias of the base table of this query.
   * @param array $options
   *   An array of options on the query.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   An appropriate SelectQuery object for this database connection. Note that
   *   it may be a driver-specific subclass of SelectQuery, depending on the
   *   driver.
   *
   * @see \Drupal\Core\Database\Query\Select
   */
  public function select($table, $alias = NULL, array $options = []);

  /**
   * Prepares and returns an INSERT query object.
   *
   * @param string $table
   *   The table to use for the insert statement.
   * @param array $options
   *   (optional) An associative array of options to control how the query is
   *   run. The given options will be merged with
   *   \Drupal\Core\Database\Connection::defaultOptions().
   *
   * @return \Drupal\Core\Database\Query\Insert
   *   A new Insert query object.
   *
   * @see \Drupal\Core\Database\Query\Insert
   * @see \Drupal\Core\Database\Connection::defaultOptions()
   */
  public function insert($table, array $options = []);

  /**
   * Returns the ID of the last inserted row or sequence value.
   *
   * This method should normally be used only within database driver code.
   *
   * This is a proxy to invoke lastInsertId() from the wrapped connection.
   * If a sequence name is not specified for the name parameter, this returns a
   * string representing the row ID of the last row that was inserted into the
   * database.
   * If a sequence name is specified for the name parameter, this returns a
   * string representing the last value retrieved from the specified sequence
   * object.
   *
   * @param string|null $name
   *   (Optional) Name of the sequence object from which the ID should be
   *   returned.
   *
   * @return string
   *   The value returned by the wrapped connection.
   *
   * @throws \Drupal\Core\Database\DatabaseExceptionWrapper
   *   In case of failure.
   */
  public function lastInsertId(?string $name = NULL): string;

  /**
   * Prepares and returns a MERGE query object.
   *
   * @param string $table
   *   The table to use for the merge statement.
   * @param array $options
   *   (optional) An array of options on the query.
   *
   * @return \Drupal\Core\Database\Query\Merge
   *   A new Merge query object.
   *
   * @see \Drupal\Core\Database\Query\Merge
   */
  public function merge($table, array $options = []);

  /**
   * Prepares and returns an UPSERT query object.
   *
   * @param string $table
   *   The table to use for the upsert query.
   * @param array $options
   *   (optional) An array of options on the query.
   *
   * @return \Drupal\Core\Database\Query\Upsert
   *   A new Upsert query object.
   *
   * @see \Drupal\Core\Database\Query\Upsert
   */
  public function upsert($table, array $options = []);

  /**
   * Prepares and returns an UPDATE query object.
   *
   * @param string $table
   *   The table to use for the update statement.
   * @param array $options
   *   (optional) An associative array of options to control how the query is
   *   run. The given options will be merged with
   *   \Drupal\Core\Database\Connection::defaultOptions().
   *
   * @return \Drupal\Core\Database\Query\Update
   *   A new Update query object.
   *
   * @see \Drupal\Core\Database\Query\Update
   * @see \Drupal\Core\Database\Connection::defaultOptions()
   */
  public function update($table, array $options = []);

  /**
   * Prepares and returns a DELETE query object.
   *
   * @param string $table
   *   The table to use for the delete statement.
   * @param array $options
   *   (optional) An associative array of options to control how the query is
   *   run. The given options will be merged with
   *   \Drupal\Core\Database\Connection::defaultOptions().
   *
   * @return \Drupal\Core\Database\Query\Delete
   *   A new Delete query object.
   *
   * @see \Drupal\Core\Database\Query\Delete
   * @see \Drupal\Core\Database\Connection::defaultOptions()
   */
  public function delete($table, array $options = []);

  /**
   * Prepares and returns a TRUNCATE query object.
   *
   * @param string $table
   *   The table to use for the truncate statement.
   * @param array $options
   *   (optional) An array of options on the query.
   *
   * @return \Drupal\Core\Database\Query\Truncate
   *   A new Truncate query object.
   *
   * @see \Drupal\Core\Database\Query\Truncate
   */
  public function truncate($table, array $options = []);

  /**
   * Prepares and returns a CONDITION query object.
   *
   * @param string $conjunction
   *   The operator to use to combine conditions: 'AND' or 'OR'.
   *
   * @return \Drupal\Core\Database\Query\Condition
   *   A new Condition query object.
   *
   * @see \Drupal\Core\Database\Query\Condition
   */
  public function condition($conjunction);

  /**
   * Returns a DatabaseSchema object for manipulating the schema.
   *
   * This method will lazy-load the appropriate schema library file.
   *
   * @return \Drupal\Core\Database\Schema
   *   The database Schema object for this connection.
   */
  public function schema();

  /**
   * Escapes a table name string.
   *
   * Force all table names to be strictly alphanumeric-plus-underscore.
   * Database drivers should never wrap the table name in database-specific
   * escape characters. This is done in Connection::prefixTables(). The
   * database-specific escape characters are added in Connection::setPrefix().
   *
   * @param string $table
   *   An unsanitized table name.
   *
   * @return string
   *   The sanitized table name.
   *
   * @see \Drupal\Core\Database\Connection::prefixTables()
   * @see \Drupal\Core\Database\Connection::setPrefix()
   */
  public function escapeTable($table);

  /**
   * Dispatches a database API event via the container dispatcher.
   *
   * @param \Drupal\Core\Database\Event\DatabaseEvent $event
   *   The database event.
   * @param string|null $eventName
   *   (Optional) the name of the event to dispatch.
   *
   * @return \Drupal\Core\Database\Event\DatabaseEvent
   *   The database event.
   *
   * @throws \Drupal\Core\Database\Exception\EventException
   *   If the container is not initialized.
   */
  public function dispatchEvent(DatabaseEvent $event, ?string $eventName = NULL): DatabaseEvent;

  /**
   * Disables database API events dispatching.
   *
   *  The return type might appear counter-intuitive but is a BC layer for
   *  connections which only type hint a return type of static. With the
   *  introduction of connection decorators (e.g., for non-transactional
   *  connections) the return type must be covariant, but can be narrowed on
   *  implementing classes.
   *
   * @param string[] $eventNames
   *   A list of database events to be disabled.
   *
   * @return \Drupal\Core\Database\Connection|static
   *   The connection.
   */
  public function disableEvents(array $eventNames): DatabaseConnectionInterface|static;

  /**
   * Enables database API events dispatching.
   *
   * The return type might appear counter-intuitive but is a BC layer for
   * connections which only type hint a return type of static. With the
   * introduction of connection decorators (e.g., for non-transactional
   * connections) the return type must be covariant, but can be narrowed on
   * implementing classes.
   *
   * @param string[] $eventNames
   *   A list of database events to be enabled.
   *
   * @return \Drupal\Core\Database\Connection|static
   *   The connection.
   */
  public function enableEvents(array $eventNames): DatabaseConnectionInterface|static;

  /**
   * Returns the status of a database API event toggle.
   *
   * @param string $eventName
   *   The name of the event to check.
   *
   * @return bool
   *   TRUE if the event is going to be fired by the database API, FALSE
   *   otherwise.
   */
  public function isEventEnabled(string $eventName): bool;

  /**
   * Indicates if the driver allows a concurrent non-transactional connection.
   *
   * @return bool
   *   TRUE if this connection allows a concurrent non-transactional
   *   connection, FALSE otherwise.
   */
  public function allowsConcurrentNonTransactionalConnection(): bool;

}
