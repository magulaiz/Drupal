<?php

namespace Drupal\Core\Database\Query;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Identifier\Table as TableIdentifier;

/**
 * General class for an abstracted DELETE operation.
 *
 * @ingroup database
 */
class Delete extends Query implements ConditionInterface {

  use QueryConditionTrait;

  /**
   * The table from which to delete.
   */
  protected TableIdentifier $tableIdentifier;

  /**
   * Constructs a Delete object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A Connection object.
   * @param string|\Drupal\Core\Database\Identifier\Table $table
   *   Name of the table to associate with this query.
   * @param array $options
   *   Array of database options.
   */
  public function __construct(Connection $connection, $table, array $options = []) {
    parent::__construct($connection, $options);
    if (!$table instanceof TableIdentifier) {
      $table = $this->connection->identifiers->table($table);
    }
    assert($table instanceof TableIdentifier);
    $this->tableIdentifier = $table;

    $this->condition = $this->connection->condition('AND');
  }

  /**
   * Implements the magic __get() method.
   */
  public function __get(string $name): mixed {
    switch ($name) {
      case 'table':
        @trigger_error("Accessing Connection::\$table is deprecated in drupal:11.2.0 and the property is removed from drupal:12.0.0. Use \$tableIdentifier instead. See https://www.drupal.org/node/7654123", E_USER_DEPRECATED);
        return $this->tableIdentifier->identifier;

      default:
        throw new \LogicException("The \${$name} property is undefined in " . __CLASS__);

    }
  }

  /**
   * Implements the magic __set() method.
   */
  public function __set(string $name, mixed $value): void {
    switch ($name) {
      case 'table':
        @trigger_error("Accessing Connection::\$table is deprecated in drupal:11.2.0 and the property is removed from drupal:12.0.0. Use \$tableIdentifier instead. See https://www.drupal.org/node/7654123", E_USER_DEPRECATED);
        $this->tableIdentifier->identifier = $this->connection->identifiers->table($value);
        break;

      default:
        throw new \LogicException("The \${$name} property is undefined in " . __CLASS__);

    }
  }

  /**
   * Implements the magic __isset() method.
   */
  public function __isset(string $name): bool {
    switch ($name) {
      case 'table':
        @trigger_error("Accessing Connection::\$table is deprecated in drupal:11.2.0 and the property is removed from drupal:12.0.0. Use \$tableIdentifier instead. See https://www.drupal.org/node/7654123", E_USER_DEPRECATED);
        return isset($this->tableIdentifier);

      default:
        throw new \LogicException("The \${$name} property is undefined in " . __CLASS__);

    }
  }

  /**
   * Implements the magic __unset() method.
   */
  public function __unset(string $name): void {
    switch ($name) {
      case 'table':
        @trigger_error("Accessing Connection::\$table is deprecated in drupal:11.2.0 and the property is removed from drupal:12.0.0. Use \$tableIdentifier instead. See https://www.drupal.org/node/7654123", E_USER_DEPRECATED);
        unset($this->tableIdentifier);
        break;

      default:
        throw new \LogicException("The \${$name} property is undefined in " . __CLASS__);

    }
  }

  /**
   * Executes the DELETE query.
   *
   * @return int
   *   The number of rows affected by the delete query.
   */
  public function execute() {
    $values = [];
    if (count($this->condition)) {
      $this->condition->compile($this->connection, $this);
      $values = $this->condition->arguments();
    }

    $stmt = $this->connection->prepareStatement((string) $this, $this->queryOptions, TRUE);
    try {
      $stmt->execute($values, $this->queryOptions);
      return $stmt->rowCount();
    }
    catch (\Exception $e) {
      $this->connection->exceptionHandler()->handleExecutionException($e, $stmt, $values, $this->queryOptions);
    }
  }

  /**
   * Implements PHP magic __toString method to convert the query to a string.
   *
   * @return string
   *   The prepared statement.
   */
  public function __toString() {
    // Create a sanitized comment string to prepend to the query.
    $comments = $this->connection->makeComment($this->comments);

    $query = $comments . "DELETE FROM $this->tableIdentifier";

    if (count($this->condition)) {

      $this->condition->compile($this->connection, $this);
      $query .= "\nWHERE " . $this->condition;
    }

    return $query;
  }

}
