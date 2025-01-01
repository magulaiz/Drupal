<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Statement;

abstract class DqlResultBase {

  /**
   * Holds the default fetch mode.
   */
  protected FetchAs $defaultFetchMode = FetchAs::Object;

  /**
   * Holds the fetch options.
   *
   * @var array{'class': class-string, 'constructor_args': array<mixed>, 'column': int}
   */
  protected array $fetchOptions = [
    'class' => 'stdClass',
    'constructor_args' => [],
    'column' => 0,
  ];

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
