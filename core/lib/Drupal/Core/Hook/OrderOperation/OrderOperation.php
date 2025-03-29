<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Base class for order operations.
 */
abstract class OrderOperation {

  /**
   * Packs an order operation object.
   *
   * @return array
   *   Packed operation.
   */
  final public function pack(): array {
    $is_before_or_after = match(get_class($this)) {
      BeforeOrAfter::class => TRUE,
      FirstOrLast::class => FALSE,
    };
    return [$is_before_or_after, get_object_vars($this)];
  }

  /**
   * Unpacks an order operation object.
   *
   * @param array $packed_operation
   *   Packed operation.
   *
   * @return self
   *   Unpacked operation.
   */
  final public static function unpack(array $packed_operation): self {
    [$is_before_or_after, $args] = $packed_operation;
    $class = $is_before_or_after ? BeforeOrAfter::class : FirstOrLast::class;
    return new $class(...$args);
  }

}
