<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Statement;

/**
 * A trait for calling \PDOStatement methods.
 */
trait PdoTrait {

  protected function fetchAsToPdo(FetchAs $mode): int {
    return match ($mode) {
      FetchAs::Associative => \PDO::FETCH_ASSOC,
      FetchAs::ClassObject => \PDO::FETCH_CLASS,
      FetchAs::Column => \PDO::FETCH_COLUMN,
      FetchAs::List => \PDO::FETCH_NUM,
      FetchAs::Object => \PDO::FETCH_OBJ,
    };
  }

  protected function pdoToFetchAs(int $mode): FetchAs {
    return match ($mode) {
      \PDO::FETCH_ASSOC => FetchAs::Associative,
      \PDO::FETCH_CLASS, \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE => FetchAs::ClassObject,
      \PDO::FETCH_COLUMN => FetchAs::Column,
      \PDO::FETCH_NUM => FetchAs::List,
      \PDO::FETCH_OBJ => FetchAs::Object,
      default => throw new \RuntimeException('Fetch mode ' . ($this->fetchModeLiterals[$mode] ?? $mode) . ' is not supported. Use supported modes only.'),
    };
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
  public function getClientStatement(): \PDOStatement {
    if ($this->clientStatement) {
      assert($this->clientStatement instanceof \PDOStatement);
      return $this->clientStatement;
    }
    throw new \RuntimeException('\\PDOStatement not initialized');
  }

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
  protected function clientSetFetchMode(FetchAs $mode, int|string|null $columnOrClass = NULL, array|null $constructorArguments = NULL): bool {
    return match ($mode) {
      FetchAs::Column => $this->getClientStatement()->setFetchMode(
        \PDO::FETCH_COLUMN,
        $columnOrClass ?? $this->fetchOptions['column'],
      ),
      FetchAs::ClassObject => $this->getClientStatement()->setFetchMode(
        \PDO::FETCH_CLASS,
        $columnOrClass ?? $this->fetchOptions['class'],
        $constructorArguments ?? $this->fetchOptions['constructor_args'],
      ),
      default => $this->getClientStatement()->setFetchMode(
        $this->fetchAsToPdo($mode),
      ),
    };
  }

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
  protected function clientExecute(?array $arguments = [], array $options = []): bool {
    return $this->getClientStatement()->execute($arguments);
  }

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
  protected function clientFetch(?FetchAs $mode = NULL, ?int $cursorOrientation = NULL, ?int $cursorOffset = NULL) {
    return $this->getClientStatement()->fetch(
      $mode ? $this->fetchAsToPdo($mode) : \PDO::FETCH_DEFAULT,
      $cursorOrientation ?? \PDO::FETCH_ORI_NEXT,
      $cursorOffset ?? 0,
    );
  }

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
  protected function clientFetchColumn(int $column = 0): mixed {
    return $this->getClientStatement()->fetchColumn($column);
  }

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
  protected function clientFetchAll(?FetchAs $mode = NULL, int|string|null $columnOrClass = NULL, array|null $constructorArguments = NULL): array {
    return match ($mode) {
      FetchAs::Column => $this->getClientStatement()->fetchAll(
        \PDO::FETCH_COLUMN,
        $columnOrClass ?? $this->fetchOptions['column'],
      ),
      FetchAs::ClassObject => $this->getClientStatement()->fetchAll(
        \PDO::FETCH_CLASS,
        $columnOrClass ?? $this->fetchOptions['class'],
        $constructorArguments ?? $this->fetchOptions['constructor_args'],
      ),
      default => $this->getClientStatement()->fetchAll(
        $mode ? $this->fetchAsToPdo($mode) : $this->fetchAsToPdo($this->defaultFetchMode),
      ),
    };
  }

  /**
   * Returns the number of rows affected by the last SQL statement.
   *
   * @return int
   *   The number of rows.
   */
  protected function clientRowCount(): int {
    return $this->getClientStatement()->rowCount();
  }

  /**
   * Returns the query string used to prepare the statement.
   *
   * @return string
   *   The query string.
   */
  protected function clientQueryString(): string {
    return $this->getClientStatement()->queryString;
  }

}
