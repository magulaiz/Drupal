<?php

namespace Drupal\Core\Database\Statement;

class PrefetchedResult {

  public readonly array $columnNames;
  protected ?int $currentRowIndex = NULL;

  public function __construct(
    protected array $data,
    public readonly ?int $rowCount,
  ) {
    $this->columnNames = isset($this->data[0]) ? array_keys($this->data[0]) : [];
    $this->currentRowIndex = -1;
  }

  public function fetchOne(): array|NULL {
    $this->currentRowIndex++;
    if(!isset($this->data[$this->currentRowIndex])) {
      $this->currentRowIndex = NULL;
      return NULL;
    }
    $row = $this->data[$this->currentRowIndex];
    unset($this->data[$this->currentRowIndex]);
    return $row;
  }

}
