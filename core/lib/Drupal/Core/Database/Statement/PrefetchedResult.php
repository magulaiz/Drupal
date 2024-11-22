<?php

namespace Drupal\Core\Database\Statement;

class PrefetchedResult {

  public function __construct(
    public array $data,
    public readonly array $columnNames,
    public readonly ?int $rowCount,
  ) {
  }

}
