<?php

namespace Drupal\Core\Database;

use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\Core\Database\Event\StatementExecutionFailureEvent;
use Drupal\Core\Database\Event\StatementExecutionStartEvent;
use Drupal\Core\Database\Statement\FetchAs;
use Drupal\Core\Database\Statement\PdoResult;
use Drupal\Core\Database\Statement\PdoTrait;
use Drupal\Core\Database\Statement\StatementBase;

// cSpell:ignore maxlen driverdata INOUT

/**
 * StatementInterface iterator implementation.
 */
class StatementWrapperIterator extends StatementBase {

  use PdoTrait;

  /**
   * Holds the default fetch mode.
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use
   * $fetchMode instead.
   *
   * @see https://www.drupal.org/node/3510455
   */
  protected FetchAs $defaultFetchMode = FetchAs::Object;

  /**
   * Holds fetch options.
   *
   * @var array{'class': class-string, 'constructor_args': array<mixed>, 'column': int}
   *
   * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use
   * the methods provided by Drupal\Core\Database\Statement\PrefetchedResult
   * instead.
   *
   * @see https://www.drupal.org/node/3510455
   */
  protected array $fetchOptions = [
    'class' => 'stdClass',
    'constructor_args' => [],
    'column' => 0,
  ];

  /**
   * Constructs a StatementWrapperIterator object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param object $clientConnection
   *   Client database connection object, for example \PDO.
   * @param string $query
   *   The SQL query string.
   * @param array $options
   *   Array of query options.
   * @param bool $rowCountEnabled
   *   (optional) Enables counting the rows matched. Defaults to FALSE.
   */
  public function __construct(
    Connection $connection,
    object $clientConnection,
    string $query,
    array $options,
    bool $rowCountEnabled = FALSE,
  ) {
    parent::__construct($connection, $clientConnection, $query, $rowCountEnabled);
    $this->clientStatement = $this->clientConnection->prepare($query, $options);
    $this->setFetchMode(FetchAs::Object);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($args = [], $options = []) {
    if (isset($options['fetch']) && is_int($options['fetch'])) {
      @trigger_error("Passing the 'fetch' key as an integer to \$options in execute() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
    }

    if (isset($options['fetch'])) {
      if (is_string($options['fetch'])) {
        $this->setFetchMode(FetchAs::ClassObject, $options['fetch']);
      }
      else {
        $this->setFetchMode($options['fetch']);
      }
    }

    // Dispatch an event informing that the statement execution begins.
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
      $this->result = new PdoResult(
        $this->fetchMode,
        $this->fetchOptions,
        $this->clientStatement,
      );
      $this->markResultsetIterable($return);
    }
    catch (\Exception $e) {
      // On execution failure, dispatch an event informing of the situation.
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

    // Dispatch an event informing that the statement execution succeeded.
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

}
