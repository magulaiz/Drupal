<?php

declare(strict_types=1);

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
   * The results of a data query language (DQL) statement.
   */
  protected ?DqlResultBase $result = NULL;

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
   * @param object $clientConnection
   *   Client database connection object, for example \PDO.
   * @param bool $rowCountEnabled
   *   (optional) Enables counting the rows matched. Defaults to FALSE.
   */
  public function __construct(
    protected readonly Connection $connection,
    protected readonly object $clientConnection,
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
  abstract public function execute($args = [], $options = []);

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
    assert($fetch === NULL || $fetch instanceof FetchAs);
    return $this->result->fetchAllAssoc($key, $fetch ?? $this->defaultFetchMode, $this->fetchOptions);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllKeyed($keyIndex = 0, $valueIndex = 1) {
    return $this->result->fetchAllKeyed($keyIndex, $valueIndex);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchField($index = 0) {
    $column = $this->result->fetch(FetchAs::Column, ['column' => $index]);

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
    $row = $className === NULL ?
      $this->result->fetch(FetchAs::Object, []) :
      $this->result->fetch(FetchAs::ClassObject, [
        'class' => $className,
        'constructor_args' => $constructorArguments,
      ]);

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
      return $this->result->rowCount;
    }
    else {
      throw new RowCountException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setFetchMode($mode, $a1 = NULL, $a2 = []) {
    assert($mode instanceof FetchAs);
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
    try {
      return $this->clientSetFetchMode($mode, $a1, $a2);
    }
    catch (\LogicException) {
      // The client statement is missing, just do with the properties setting.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($mode = NULL, $cursorOrientation = NULL, $cursorOffset = NULL) {
    assert($mode === NULL || $mode instanceof FetchAs);
    $row = $this->result->fetch($mode ?? $this->defaultFetchMode, $this->fetchOptions);

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
    assert($mode === NULL || $mode instanceof FetchAs);
    $fetchMode = $mode ?? $this->defaultFetchMode;
    if (isset($columnIndex)) {
      $this->fetchOptions['column'] = $columnIndex;
    }
    if (isset($constructorArguments)) {
      $this->fetchOptions['constructor_args'] = $constructorArguments;
    }

    $return = $this->result->fetchAll($fetchMode, $this->fetchOptions);

    $this->markResultsetFetchingComplete();

    return $return;
  }

}
