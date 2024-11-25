<?php

namespace Drupal\Core\Database\Statement;

use Drupal\Core\Database\FetchModeTrait;

class PrefetchedResult {

  use FetchModeTrait;

  public readonly array $columnNames;
  protected ?int $currentRowIndex = NULL;

  public function __construct(
    protected array $data,
    public readonly ?int $rowCount,
  ) {
    $this->columnNames = isset($this->data[0]) ? array_keys($this->data[0]) : [];
    $this->currentRowIndex = -1;
  }

  public function fetch(FetchAs $mode, array $fetchOptions): array|object|int|float|string|bool|NULL {
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
