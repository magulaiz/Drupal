<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Statement;

use Drupal\Core\Database\FetchModeTrait;

class PrefetchedResult extends DqlResultBase {

  use FetchModeTrait;

  /**
   * The column names.
   */
  public readonly array $columnNames;

  /**
   * The current row index in the result set.
   */
  protected ?int $currentRowIndex = NULL;

  /**
   * Constructor.
   *
   * @param array $data
   *   Data.
   * @param int|null $rowCount
   *   The row count.
   */
  public function __construct(
    protected array $data,
    public readonly ?int $rowCount,
  ) {
    $this->columnNames = isset($this->data[0]) ? array_keys($this->data[0]) : [];
    $this->currentRowIndex = -1;
  }

  public function setFetchMode(FetchAs $mode, array $fetchOptions = []): bool {
    // @todo fix this.
    return TRUE;
  }

  public function fetch(FetchAs $mode, array $fetchOptions = []): array|object|int|float|string|bool|NULL {
    $this->currentRowIndex++;
    if (!isset($this->data[$this->currentRowIndex])) {
      $this->currentRowIndex = NULL;
      return FALSE;
    }
    $rowAssoc = $this->data[$this->currentRowIndex];
    unset($this->data[$this->currentRowIndex]);
    return $this->assocToFetchMode($rowAssoc, $mode, $fetchOptions);
  }

  public function fetchAll(FetchAs $mode, array $fetchOptions): array {
    $result = [];
    while ($rowAssoc = $this->fetch(FetchAs::Associative, $fetchOptions)) {
      $result[] = $this->assocToFetchMode($rowAssoc, $mode, $fetchOptions);
    }
    return $result;
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

}
