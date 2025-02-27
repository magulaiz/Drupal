<?php

declare(strict_types=1);

namespace Drupal\mysqli\Driver\Database\mysqli;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\Core\Database\Event\StatementExecutionFailureEvent;
use Drupal\Core\Database\Event\StatementExecutionStartEvent;
use Drupal\Core\Database\RowCountException;
use Drupal\Core\Database\Statement\FetchAs;
use Drupal\Core\Database\StatementWrapperIterator;

/**
 * MySQLi implementation of \Drupal\Core\Database\Query\StatementInterface.
 */
class Statement extends StatementWrapperIterator {

  /**
   * Holds the index position of named parameters.
   *
   * The mysqli driver only allows positional placeholders '?', whereas in
   * Drupal the SQL is generated with named placeholders ':name'. In order to
   * execute the SQL, the string containing the named placeholders is converted
   * to using positional ones, and the position (index) of each named
   * placeholder in the string is stored here.
   */
  protected array $paramsPositions;

  /**
   * The default fetch mode.
   *
   * See http://php.net/manual/pdo.constants.php for the definition of the
   * constants used.
   */
  protected int $defaultFetchStyle;

  /**
   * Holds fetch options.
   *
   * @var string[]
   */
  protected array $fetchOptions = [
    'class' => 'stdClass',
    'constructor_args' => [],
    'column' => 0,
  ];

  /**
   * The mysqli result object.
   *
   * Stores results of a data selection query.
   */
  protected ?\mysqli_result $mysqliResult;

  /**
   * Constructs a Statement object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param \mysqli $mysqliConnection
   *   Client database connection object.
   * @param string $queryString
   *   The SQL query string.
   * @param array $driverOpts
   *   (optional) Array of query options.
   * @param bool $rowCountEnabled
   *   (optional) Enables counting the rows affected. Defaults to FALSE.
   */
  public function __construct(
    protected readonly Connection $connection,
    protected readonly \mysqli $mysqliConnection,
    protected string $queryString,
    protected array $driverOpts = [],
    protected readonly bool $rowCountEnabled = FALSE,
  ) {
    $this->setFetchMode(FetchAs::Object);
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
        $args,
        $this->connection->findCallerFromDebugBacktrace(),
      );
      $this->connection->dispatchEvent($startEvent);
    }

    try {
      // Prepare the lower-level statement if it's not been prepared already.
      if (!isset($this->clientStatement)) {
        // Replace named placeholders with positional ones if needed.
        $this->paramsPositions = array_flip(array_keys($args));
        $converter = new NamedPlaceholderConverter();
        $converter->parse($this->queryString, $args);
        [$this->queryString, $args] = [$converter->getConvertedSQL(), $converter->getConvertedParameters()];
        $this->clientStatement = $this->mysqliConnection->prepare($this->queryString);
      }
      else {
        // Transform the $args to positional.
        $tmp = [];
        foreach ($this->paramsPositions as $param => $pos) {
          $tmp[$pos] = $args[$param];
        }
        $args = $tmp;
      }

      // In mysqli, the results of the statement execution are returned in a
      // different object than the statement itself.
      $return = $this->clientStatement->execute($args);
      $this->markResultsetIterable($return);
      $result = $this->clientStatement->get_result();
      $this->mysqliResult = $result !== FALSE ? $result : NULL;
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
        $startEvent->time,
      ));
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryString() {
    return $this->queryString;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllAssoc($key, $fetch = NULL) {
    $return = [];
    if (isset($fetch)) {
      if (is_string($fetch)) {
        $this->setFetchMode(FetchAs::ClassObject, $fetch);
      }
      else {
        $this->setFetchMode($fetch ?: $this->defaultFetchStyle);
      }
    }

    while ($record = $this->fetch()) {
      $record_key = is_object($record) ? $record->$key : $record[$key];
      $return[$record_key] = $record;
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllKeyed($key_index = 0, $value_index = 1) {
    $return = [];
    $this->setFetchMode(FetchAs::Associative);
    while ($record = $this->fetch(FetchAs::Associative)) {
      $cols = array_keys($record);
      $return[$record[$cols[$key_index]]] = $record[$cols[$value_index]];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchField($index = 0) {
    if (($ret = $this->fetch(FetchAs::List)) === FALSE) {
      return FALSE;
    }
    return $ret[$index] === NULL ? NULL : (string) $ret[$index];
  }

  /**
   * {@inheritdoc}
   */
  public function fetchObject(?string $class_name = NULL, array $constructor_arguments = []) {
    if (isset($class_name)) {
      $this->defaultFetchStyle = FetchAs::ClassObject;
      $this->fetchOptions = [
        'class' => $class_name,
        'constructor_args' => $constructor_arguments,
      ];
    }
    return $this->fetch($class_name ?? FetchAs::Object);
  }

  /**
   * {@inheritdoc}
   */
  public function rowCount() {
    // SELECT query should not use this method.
    if ($this->rowCountEnabled) {
      // @todo The most accurate value to return for Drupal here is the first
      //   occurrence of an integer in the string stored by the connection's
      //   $info property.
      //   This is something like 'Rows matched: 1  Changed: 1  Warnings: 0' for
      //   UPDATE or DELETE operations, and
      //   'Records: 2  Duplicates: 1  Warnings: 0' for INSERT ones.
      //   This however requires a regex parsing of the string which is
      //   expensive; $affected_rows would be less accurate but much faster. We
      //   would need Drupal to be less strict in testing, and never rely on
      //   this value in runtime (which would be healthy anyway).
      if ($this->mysqliConnection->info !== NULL) {
        $matches = [];
        if (preg_match('/\s(\d+)\s/', $this->mysqliConnection->info, $matches) === 1) {
          return (int) $matches[0];
        }
        else {
          throw new DatabaseExceptionWrapper('Invalid data in the $info property of the mysqli connection - ' . $this->mysqliConnection->info);
        }
      }
      elseif ($this->mysqliConnection->affected_rows !== NULL) {
        return $this->mysqliConnection->affected_rows;
      }
      throw new DatabaseExceptionWrapper('Unable to retrieve affected rows data');
    }
    else {
      throw new RowCountException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFetchMode($mode, $a1 = NULL, $a2 = []): bool {
    if (is_int($mode)) {
      throw new DatabaseExceptionWrapper("Passing the \$mode argument as an integer to setFetchMode() is not supported. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
    }
    $this->defaultFetchStyle = $mode;
    switch ($mode) {
      case FetchAs::ClassObject:
        $this->fetchOptions['class'] = $a1;
        if ($a2) {
          $this->fetchOptions['constructor_args'] = $a2;
        }
        break;

      case FetchAs::Column:
        $this->fetchOptions['column'] = $a1;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($mode = NULL, $cursor_orientation = NULL, $cursor_offset = NULL) {
    if (is_string($mode)) {
      $this->setFetchMode(FetchAs::ClassObject, $mode);
      $mode = FetchAs::ClassObject;
    }
    else {
      if (is_int($mode)) {
        throw new DatabaseExceptionWrapper("Passing the \$mode argument as an integer to fetch() is not supported. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
      }
      $mode = $mode ?: $this->defaultFetchStyle;
    }

    $mysqli_row = $this->mysqliResult->fetch_assoc();

    if (!$mysqli_row) {
      $this->markResultsetFetchingComplete();
      return FALSE;
    }

    $columnNames = array_keys($mysqli_row);

    // Stringify all non-NULL column values.
    $row = [];
    foreach ($mysqli_row as $column => $value) {
      $row[$column] = $value === NULL ? NULL : (string) $value;
    }

    $returnValue = match($mode) {
      FetchAs::Associative => $row,
      FetchAs::List => $this->assocToNum($row),
      FetchAs::Object => $this->assocToObj($row),
      FetchAs::ClassObject => $this->assocToClass($row, $this->fetchOptions['class'], $this->fetchOptions['constructor_args']),
      FetchAs::Column=> $this->assocToColumn($row, $columnNames, $this->fetchOptions['column']),
      default => throw new DatabaseExceptionWrapper('Fetch mode ' . ($this->fetchModeLiterals[$mode] ?? $mode) . ' is not supported.'),
    };

    $this->setResultsetCurrentRow($returnValue);
    return $returnValue;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAll($mode = NULL, $column_index = NULL, $constructor_arguments = NULL) {
    if (is_int($mode)) {
      throw new DatabaseExceptionWrapper("Passing the \$mode argument as an integer to fetchAll() is not supported. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
    }
    if (is_string($mode)) {
      $this->setFetchMode(FetchAs::ClassObject, $mode);
      $mode = FetchAs::ClassObject;
    }
    else {
      $mode = $mode ?: $this->defaultFetchStyle;
    }

    $rows = [];
    if (FetchAs::Column== $mode) {
      // When fetching a column's value across the entire dataset, fetch
      // through it and pick the requested column value for each row.
      if ($column_index === NULL) {
        $column_index = 0;
      }
      while (($record = $this->fetch(FetchAs::Associative)) !== FALSE) {
        $cols = array_keys($record);
        $rows[] = $record[$cols[$column_index]];
      }
    }
    else {
      while (($row = $this->fetch($mode)) !== FALSE) {
        $rows[] = $row;
      }
    }

    return $rows;
  }

}
