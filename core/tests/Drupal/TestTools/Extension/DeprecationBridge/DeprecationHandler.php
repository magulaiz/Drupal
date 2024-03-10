<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

/**
 * @todo
 *
 * @internal
 */
final class DeprecationHandler {

  public static function currentErrorHandler(): ?callable {
    $currentHandler = set_error_handler('var_dump');
    restore_error_handler();
    return $currentHandler;
  }

  /**
   * @todo for debugging. Remove eventually.
   */
  public static function dumpErrorHandler($msg): void {
    $handler = self::currentErrorHandler();
    dump([$msg, (is_object($handler) ? get_class($handler) : $handler)]);
  }

}
