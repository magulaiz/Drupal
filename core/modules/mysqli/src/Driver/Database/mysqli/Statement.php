<?php

declare(strict_types=1);

namespace Drupal\mysqli\Driver\Database\mysqli;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\Event\StatementExecutionEndEvent;
use Drupal\Core\Database\Event\StatementExecutionFailureEvent;
use Drupal\Core\Database\Event\StatementExecutionStartEvent;
use Drupal\Core\Database\Statement\FetchAs;
use Drupal\Core\Database\Statement\StatementBase;

/**
 * MySQLi implementation of \Drupal\Core\Database\Query\StatementInterface.
 */
class Statement extends StatementBase {

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
   * Constructs a Statement object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Drupal database connection object.
   * @param \mysqli $clientConnection
   *   Client database connection object.
   * @param string $queryString
   *   The SQL query string.
   * @param array $driverOpts
   *   (optional) Array of query options.
   * @param bool $rowCountEnabled
   *   (optional) Enables counting the rows affected. Defaults to FALSE.
   */
  public function __construct(
    Connection $connection,
    \mysqli $clientConnection,
    string $queryString,
    protected array $driverOpts = [],
    bool $rowCountEnabled = FALSE,
  ) {
    parent::__construct($connection, $clientConnection, $queryString, $rowCountEnabled);
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

    // Dispatch an event informing that the statement execution begins.
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
        [$convertedQueryString, $args] = [$converter->getConvertedSQL(), $converter->getConvertedParameters()];
        $this->clientStatement = $this->clientConnection->prepare($convertedQueryString);
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
      $this->result = new Result(
        $this->fetchMode,
        $this->fetchOptions,
        $this->clientStatement->get_result(),
        $this->clientConnection,
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
        $startEvent->time,
      ));
    }

    return $return;
  }

}
