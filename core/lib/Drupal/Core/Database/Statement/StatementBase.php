<?php

namespace Drupal\Core\Database\Statement;

use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\Core\Database\Event\StatementExecutionFailureEvent;
use Drupal\Core\Database\Event\StatementExecutionStartEvent;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\FetchModeTrait;
use Drupal\Core\Database\RowCountException;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Database\StatementIteratorTrait;

/**
 * StatementInterface base implementation.
 *
 * This class is meant to be generic enough for any type of database clients,
 * even if all Drupal core database drivers currently use PDO clients. We
 * implement \Iterator instead of \IteratorAggregate to allow iteration to be
 * kept in sync with the underlying database resultset cursor. PDO is not able
 * to execute a database operation while a cursor is open on the result of an
 * earlier select query, so Drupal by default uses buffered queries setting
 * \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY to TRUE on the connection. This forces
 * the query to return all the results in a buffer local to the client library,
 * potentially leading to memory issues in case of large datasets being
 * returned by a query. Other database clients, however, could allow
 * multithread queries, or developers could disable buffered queries in PDO:
 * in that case, this class prevents the resultset to be entirely fetched in
 * PHP memory (that an \IteratorAggregate implementation would force) and
 * therefore optimize memory usage while iterating the resultset.
 */
abstract class StatementBase implements \Iterator, StatementInterface {

  use FetchModeTrait;
  use StatementIteratorTrait;

  /**
   * Holds the default fetch mode.
   */
  protected FetchAs $defaultFetchMode = FetchAs::Object;

  /**
   * Holds fetch options.
   *
   * @var array{'class': class-string, 'constructor_args': array<mixed>, 'column': int}
   */
  protected array $fetchOptions = [
    'class' => 'stdClass',
    'constructor_args' => [],
    'column' => 0,
  ];

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param bool $rowCountEnabled
   *   (optional) Enables counting the rows matched. Defaults to FALSE.
   */
  public function __construct(
    protected readonly Connection $connection,
    protected readonly bool $rowCountEnabled = FALSE,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getConnectionTarget(): string {
    return $this->connection->getTarget();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($args = [], $options = []) {
    if (isset($options['fetch'])) {
      if (is_string($options['fetch'])) {
        $this->setFetchMode(FetchAs::ClassObject, $options['fetch']);
      }
      else {
        $this->setFetchMode($options['fetch']);
      }
    }

    if ($this->connection->isEventEnabled(StatementExecutionStartEvent::class)) {
      $startEvent = new StatementExecutionStartEvent(
        spl_object_id($this),
        $this->connection->getKey(),
        $this->connection->getTarget(),
        $this->getQueryString(),
        $args ?? [],
        $this->connection->findCallerFromDebugBacktrace()
      );
      $this->connection->dispatchEvent($startEvent);
    }

    try {
      $return = $this->clientExecute($args, $options);
      $this->markResultsetIterable($return);
    }
    catch (\Exception $e) {
      if (isset($startEvent) && $this->connection->isEventEnabled(StatementExecutionFailureEvent::class)) {
        $this->connection->dispatchEvent(new StatementExecutionFailureEvent(
          $startEvent->statementObjectId,
          $startEvent->key,
          $startEvent->target,
          $startEvent->queryString,
          $startEvent->args,
          $startEvent->caller,
          $startEvent->time,
          get_class($e),
          $e->getCode(),
          $e->getMessage(),
        ));
      }
      throw $e;
    }

    if (isset($startEvent) && $this->connection->isEventEnabled(StatementExecutionEndEvent::class)) {
      $this->connection->dispatchEvent(new StatementExecutionEndEvent(
        $startEvent->statementObjectId,
        $startEvent->key,
        $startEvent->target,
        $startEvent->queryString,
        $startEvent->args,
        $startEvent->caller,
        $startEvent->time
      ));
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryString() {
    return $this->clientQueryString();
  }

  /**
   * {@inheritdoc}
   */
  public function fetchCol($index = 0) {
    return $this->fetchAll(FetchAs::Column, $index);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllAssoc($key, $fetch = NULL) {
    if (isset($fetch)) {
      if (is_string($fetch)) {
        $this->setFetchMode(FetchAs::ClassObject, $fetch);
      }
      else {
        $this->setFetchMode($fetch);
      }
    }

    // Return early if the statement was already fully traversed.
    if (!$this->isResultsetIterable) {
      return [];
    }

    // Once the while loop is completed, the resultset is marked so not to
    // allow more fetching.
    $return = [];
    while ($record = $this->fetch()) {
      $recordKey = is_object($record) ? $record->$key : $record[$key];
      $return[$recordKey] = $record;
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllKeyed($keyIndex = 0, $valueIndex = 1) {
    $this->setFetchMode(FetchAs::List);

    // Return early if the statement was already fully traversed.
    if (!$this->isResultsetIterable) {
      return [];
    }

    // Once the while loop is completed, the resultset is marked so not to
    // allow more fetching.
    $return = [];
    while ($record = $this->fetch()) {
      $return[$record[$keyIndex]] = $record[$valueIndex];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchField($index = 0) {
    $column = $this->clientFetchColumn($index);

    if ($column === FALSE) {
      $this->markResultsetFetchingComplete();
      return FALSE;
    }

    $this->setResultsetCurrentRow($column);
    return $column;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAssoc() {
    return $this->fetch(FetchAs::Associative);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchObject(?string $className = NULL, array $constructorArguments = []) {
    $row = $this->clientFetchObject($className, $constructorArguments);

    if ($row === FALSE) {
      $this->markResultsetFetchingComplete();
      return FALSE;
    }

    $this->setResultsetCurrentRow($row);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function rowCount() {
    // SELECT query should not use the method.
    if ($this->rowCountEnabled) {
      return $this->clientRowCount();
    }
    else {
      throw new RowCountException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFetchMode($mode, $a1 = NULL, $a2 = []) {
    $this->defaultFetchMode = $mode;
    switch ($mode) {
      case FetchAs::ClassObject:
        $this->fetchOptions['class'] = $a1;
        if ($a2) {
          $this->fetchOptions['constructor_args'] = $a2;
        }
        break;

      case FetchAs::Column:
        $this->fetchOptions['column'] = $a1;
        break;

    }

    return $this->clientSetFetchMode($mode, $a1, $a2);
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($mode = NULL, $cursorOrientation = NULL, $cursorOffset = NULL) {
    $row = match(func_num_args()) {
      0 => $this->clientFetch(),
      1 => $this->clientFetch($mode),
      2 => $this->clientFetch($mode, $cursorOrientation),
      default => $this->clientFetch($mode, $cursorOrientation, $cursorOffset),
    };

    if ($row === FALSE) {
      $this->markResultsetFetchingComplete();
      return FALSE;
    }

    $this->setResultsetCurrentRow($row);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAll($mode = NULL, $columnIndex = NULL, $constructorArguments = NULL) {
    $fetchMode = $mode ?? $this->defaultFetchMode;
    if (isset($columnIndex)) {
      $this->fetchOptions['column'] = $columnIndex;
    }
    if (isset($constructorArguments)) {
      $this->fetchOptions['constructor_args'] = $constructorArguments;
    }

    $return = $this->clientFetchAll($fetchMode, $columnIndex, $constructorArguments);

    $this->markResultsetFetchingComplete();

    return $return;
  }

  /**
   * Returns the client-level database PDO statement object.
   *
   * This method should normally be used only within database driver code.
   *
   * @return \PDOStatement
   *   The client-level database PDO statement.
   *
   * @throws \RuntimeException
   *   If the client-level statement is not set.
   */
  abstract public function getClientStatement(): object;

  /**
   * Sets the default fetch mode for the PDO statement.
   *
   * @param \Drupal\Core\Database\FetchAs $mode
   *   One of the cases of the FetchAs enum.
   * @param int|class-string|null $columnOrClass
   *   If $mode is FetchAs::Column, the index of the column to fetch.
   *   If $mode is FetchAs::ClassObject, the FQCN of the object.
   * @param list<mixed>|null $constructorArguments
   *   If $mode is FetchAs::ClassObject, the arguments to pass to the
   *   constructor.
   *
   * @return bool
   *   Returns true on success or false on failure.
   */
  abstract protected function clientSetFetchMode(FetchAs $mode, int|string|null $columnOrClass = NULL, array|null $constructorArguments = NULL): bool;

  /**
   * Executes the prepared PDO statement.
   *
   * @param array|null $arguments
   *   An array of values with as many elements as there are bound parameters in
   *   the SQL statement being executed. This can be NULL.
   * @param array $options
   *   An array of options for this query.
   *
   * @return bool
   *   TRUE on success, or FALSE on failure.
   */
  abstract protected function clientExecute(?array $arguments = [], array $options = []): bool;

  /**
   * Fetches the next row from the PDO statement.
   *
   * @param \Drupal\Core\Database\FetchAs|null $mode
   *   (Optional) one of the cases of the FetchAs enum. If not specified,
   *   defaults to what is specified by setFetchMode().
   * @param int|null $cursorOrientation
   *   Not implemented in all database drivers, don't use.
   * @param int|null $cursorOffset
   *   Not implemented in all database drivers, don't use.
   *
   * @return array<string|int|float|bool>|object|false
   *   A result, formatted according to $mode, or FALSE on failure.
   */
  abstract protected function clientFetch(?FetchAs $mode = NULL, ?int $cursorOrientation = NULL, ?int $cursorOffset = NULL): array|object|string|int|float|bool;

  /**
   * Returns a single column from the next row of a result set.
   *
   * @param int $column
   *   0-indexed number of the column to retrieve from the row. If no value is
   *   supplied, the first column is fetched.
   *
   * @return string|int|float|bool|false
   *   A single column from the next row of a result set or false if there are
   *   no more rows.
   */
  abstract protected function clientFetchColumn(int $column = 0): mixed;

  /**
   * Fetches the next row and returns it as an object.
   *
   * @param class-string|null $class
   *   FQCN of the class to be instantiated.
   * @param list<mixed>|null $constructorArguments
   *   The arguments to be passed to the constructor.
   *
   * @return object|false
   *   An instance of the required class with property names that correspond
   *   to the column names, or FALSE on failure.
   */
  abstract protected function clientFetchObject(?string $class = NULL, array $constructorArguments = []): object|FALSE;

  /**
   * Returns an array containing all of the result set rows.
   *
   * @param \Drupal\Core\Database\FetchAs|null $mode
   *   (Optional) one of the cases of the FetchAs enum. If not specified,
   *   defaults to what is specified by setFetchMode().
   * @param int|class-string|null $columnOrClass
   *   If $mode is FetchAs::Column, the index of the column to fetch.
   *   If $mode is FetchAs::ClassObject, the FQCN of the object.
   * @param list<mixed>|null $constructorArguments
   *   If $mode is FetchAs::ClassObject, the arguments to pass to the
   *   constructor.
   *
   * @return array
   *   An array of results.
   */
  abstract protected function clientFetchAll(?FetchAs $mode = NULL, int|string|null $columnOrClass = NULL, array|null $constructorArguments = NULL): array;

  /**
   * Returns the number of rows affected by the last SQL statement.
   *
   * @return int
   *   The number of rows.
   */
  abstract protected function clientRowCount(): int;

  /**
   * Returns the query string used to prepare the statement.
   *
   * @return string
   *   The query string.
   */
  abstract protected function clientQueryString(): string;

}
