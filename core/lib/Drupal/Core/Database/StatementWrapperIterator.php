<?php

namespace Drupal\Core\Database;

use Drupal\Core\Database\Statement\FetchAs;
use Drupal\Core\Database\Statement\PdoTrait;
use Drupal\Core\Database\Statement\StatementBase;

/**
 * StatementInterface iterator implementation.
 */
class StatementWrapperIterator extends StatementBase {

  use PdoTrait;

  /**
   * The client database Statement object.
   *
   * For a \PDO client connection, this will be a \PDOStatement object.
   */
  protected object $clientStatement;

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
    parent::__construct($connection, $clientConnection, $rowCountEnabled);
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
    return parent::execute($args, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllAssoc($key, $fetch = NULL) {
    if (is_int($fetch)) {
      @trigger_error("Passing the \$fetch argument as an integer to fetchAllAssoc() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
      $fetch = $this->pdoToFetchAs($fetch);
    }
    return parent::fetchAllAssoc($key, $fetch);
  }

  /**
   * {@inheritdoc}
   */
  public function setFetchMode($mode, $a1 = NULL, $a2 = []) {
    if (is_int($mode)) {
      @trigger_error("Passing the \$mode argument as an integer to setFetchMode() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
      $mode = $this->pdoToFetchAs($mode);
    }
    return parent::setFetchMode($mode, $a1, $a2);
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($mode = NULL, $cursor_orientation = NULL, $cursor_offset = NULL) {
    if (is_int($mode)) {
      @trigger_error("Passing the \$mode argument as an integer to fetch() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
      $mode = $this->pdoToFetchAs($mode);
    }
    return match(func_num_args()) {
      0 => parent::fetch(),
      1 => parent::fetch($mode),
      2 => parent::fetch($mode, $cursor_orientation),
      default => parent::fetch($mode, $cursor_orientation, $cursor_offset),
    };
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAll($mode = NULL, $column_index = NULL, $constructor_arguments = NULL) {
    if (is_int($mode)) {
      @trigger_error("Passing the \$mode argument as an integer to fetchAll() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use a case of \Drupal\Core\Database\FetchAs enum instead. See https://www.drupal.org/node/3488338", E_USER_DEPRECATED);
      $mode = $this->pdoToFetchAs($mode);
    }
    return parent::fetchAll($mode, $column_index, $constructor_arguments);
  }

}
