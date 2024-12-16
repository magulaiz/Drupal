<?php

declare(strict_types=1);

namespace Drupal\dummydb\Driver\Database\dummydb;

use Drupal\Core\Database\Connection as CoreConnection;

/**
 * MySQL test implementation of \Drupal\Core\Database\Connection.
 */
class Connection extends CoreConnection {

  /**
   * {@inheritdoc}
   */
  public static function open(array &$connection_options = []) {
    return new stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function upsert($table, array $options = []) {
    return new stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function schema() {
    return new stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function queryRange($query, $from, $count, array $args = [], array $options = []) {
    return new stdClass();
  }

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return 'DummyDB';
  }

  /**
   * {@inheritdoc}
   */
  public function databaseType() {
    return 'DummyDB';
  }

  /**
   * {@inheritdoc}
   */
  public function createDatabase($database) {
    return 'dummydb';
  }

  /**
   * {@inheritdoc}
   */
  public function mapConditionOperator($operator) {
    return [];
  }

}
