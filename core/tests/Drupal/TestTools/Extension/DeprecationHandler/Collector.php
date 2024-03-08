<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationHandler;

/**
 * @todo
 *
 * @internal
 */
final class Collector {

  public static function currentErrorHandler(): ?callable {
    $currentHandler = set_error_handler('var_dump');
    restore_error_handler();
    return $currentHandler;
  }

}
