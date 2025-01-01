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

}
