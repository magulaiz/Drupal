<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Statement;

use Drupal\Core\Database\FetchModeTrait;

class PdoResult extends DqlResultBase {

  use FetchModeTrait;
  use PdoTrait;

  /**
   * The current row index in the result set.
   */
  protected ?int $currentRowIndex = NULL;

  public function __construct(
    protected readonly \PDOStatement $clientStatement,
    protected readonly bool $rowCountEnabled = FALSE,
  ) {
    $this->currentRowIndex = -1;
  }

  public function setFetchMode(FetchAs $mode, array $fetchOptions = []): bool {
    // @todo fix this.
    return $this->clientSetFetchMode($mode);
  }

  public function fetch(FetchAs $mode, array $fetchOptions = []): array|object|int|float|string|bool|NULL {
    // @todo various options.
    return $this->clientFetch($mode);
  }

  public function fetchAll(FetchAs $mode, array $fetchOptions): array {
    return $this->clientFetchAll($mode, $fetchOptions['column'] ?? $fetchOptions['class'] ?? NULL, $fetchOptions['constructor_args'] ?? NULL);
  }

  public function fetchAllKeyed(int $keyIndex = 0, int $valueIndex = 1): array {
    if (!isset($this->columnNames[$keyIndex]) || !isset($this->columnNames[$valueIndex])) {
      return [];
    }

    $key = $this->columnNames[$keyIndex];
    $value = $this->columnNames[$valueIndex];

    $result = [];
    while ($row = $this->fetch(FetchAs::Associative)) {
      $result[$row[$key]] = $row[$value];
    }
    return $result;
  }

  public function fetchAllAssoc(string $column, FetchAs $mode, array $fetchOptions): array {
    $result = [];
    while ($rowAssoc = $this->fetch(FetchAs::Associative, $fetchOptions)) {
      $result[$rowAssoc[$column]] = $this->assocToFetchMode($rowAssoc, $mode, $fetchOptions);
    }
    return $result;
  }

  protected function assocToFetchMode(array $rowAssoc, FetchAs $mode, array $fetchOptions): array|object|int|float|string|bool|NULL {
    return match($mode) {
      FetchAs::Associative => $rowAssoc,
      FetchAs::ClassObject => $this->assocToClass($rowAssoc, $fetchOptions['class'], $fetchOptions['constructor_args']),
      FetchAs::Column => $this->assocToColumn($rowAssoc, $this->columnNames, $fetchOptions['column']),
      FetchAs::List => $this->assocToNum($rowAssoc),
      FetchAs::Object => $this->assocToObj($rowAssoc),
    };
  }

}
