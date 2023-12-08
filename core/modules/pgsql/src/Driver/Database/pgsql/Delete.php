<?php

namespace Drupal\pgsql\Driver\Database\pgsql;

use Drupal\Core\Database\Query\Delete as QueryDelete;

/**
 * PostgreSQL implementation of \Drupal\Core\Database\Query\Delete.
 */
class Delete extends QueryDelete {

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, string $table, array $options = []) {
    // @todo Remove the __construct in Drupal 11.
    // @see https://www.drupal.org/project/drupal/issues/3256524
    parent::__construct($connection, $table, $options);
    unset($this->queryOptions['return']);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if ($this->connection->inTransaction()) {
      try {
        $savepoint = $this->connection->startTransaction('mimic_implicit_commit');
        $result = parent::execute();
        $savepoint->commit();
        return $result;
      }
      catch (\Exception $e) {
        if (isset($savepoint)) {
          $savepoint->rollback();
        }
        throw $e;
      }
    }
    return parent::execute();
  }

}
