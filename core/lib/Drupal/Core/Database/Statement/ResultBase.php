<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Statement;

/**
 * Base class for results of a data query language (DQL) statement.
 */
abstract class ResultBase {

  public function __construct(
    protected FetchAs $fetchMode,
    protected array $fetchOptions,
  ) {
  }

  /**
   * Returns the number of rows matched by the last SQL statement.
   *
   * @return int
   *   The number of rows matched by the last DELETE, INSERT, or UPDATE
   *   statement executed or throws \Drupal\Core\Database\RowCountException
   *   if the last executed statement was SELECT.
   *
   * @throws \Drupal\Core\Database\RowCountException
   */
  abstract public function rowCount(): ?int;

  /**
   * Sets the default fetch mode for this result set.
   *
   * @param \Drupal\Core\Database\FetchAs $mode
   *   One of the cases of the FetchAs enum.
   * @param array<string,string> $fetchOptions
   *   An array of fetch options.
   *
   * @return bool
   *   TRUE if successful, FALSE if not.
   */
  abstract public function setFetchMode(FetchAs $mode, array $fetchOptions = []): bool;

  /**
   * Fetches the next row.
   *
   * @param \Drupal\Core\Database\FetchAs $mode
   *   One of the cases of the FetchAs enum.
   * @param array<string,string> $fetchOptions
   *   An array of fetch options.
   *
   * @return array|object|int|float|string|bool|null
   *   A result, formatted according to $mode, or FALSE on failure.
   */
  abstract public function fetch(FetchAs $mode, array $fetchOptions = []): array|object|int|float|string|bool|NULL;

  /**
   * Returns an array containing all of the result set rows.
   *
   * @param \Drupal\Core\Database\FetchAs $mode
   *   One of the cases of the FetchAs enum.
   * @param array<string,string> $fetchOptions
   *   An array of fetch options.
   *
   * @return array
   *   An array of results.
   */
  abstract public function fetchAll(FetchAs $mode, array $fetchOptions): array;

  /**
   * Returns the entire result set as a single associative array.
   *
   * This method is only useful for two-column result sets. It will return an
   * associative array where the key is one column from the result set and the
   * value is another field. In most cases, the default of the first two columns
   * is appropriate.
   *
   * Note that this method will run the result set to the end.
   *
   * @param int $key_index
   *   The numeric index of the field to use as the array key.
   * @param int $value_index
   *   The numeric index of the field to use as the array value.
   *
   * @return array
   *   An associative array, or an empty array if there is no result set.
   */
  abstract public function fetchAllKeyed(int $keyIndex = 0, int $valueIndex = 1): array;

  /**
   * Returns the result set as an associative array keyed by the given column.
   *
   * If the given column appears multiple times, later records will overwrite
   * earlier ones.
   *
   * @param string $column
   *   The name of the field on which to index the array.
   * @param \Drupal\Core\Database\FetchAs $mode
   *   One of the cases of the FetchAs enum. If set to FetchAs::Associative
   *   or FetchAs::List the returned value with be an array of arrays. For any
   *   other value it will be an array of objects. If not specified, defaults to
   *   what is specified by setFetchMode().
   * @param array<string,string> $fetchOptions
   *   An array of fetch options.
   *
   * @return array
   *   An associative array, or an empty array if there is no result set.
   */
  abstract public function fetchAllAssoc(string $column, FetchAs $mode, array $fetchOptions): array;

}
