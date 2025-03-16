<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Static methods related to order operations.
 *
 * These should be used instead of serialize() or unserialize(), to avoid
 * security issues with unserialize(), and for additional validation.
 */
class OrderOperation {

  const array KNOWN_CLASSES = [
    'absolute' => FirstOrLast::class,
    'relative' => BeforeOrAfter::class,
  ];

  /**
   * Serializes an order operation object.
   *
   * @param \Drupal\Core\Hook\OrderOperation\OrderOperationInterface $operation
   *   Order operation object.
   *
   * @return array
   *   Packed operation.
   */
  public static function pack(OrderOperationInterface $operation): array {
    $type = array_search(get_class($operation), static::KNOWN_CLASSES)
      ?: throw new \InvalidArgumentException('Unsupported order operation class ' . get_class($operation));
    return [$type, $operation->pack()];
  }

  /**
   * Unserializes an order operation object.
   *
   * @param array $packed_operation
   *   Packed operation.
   *
   * @return \Drupal\Core\Hook\OrderOperation\OrderOperationInterface
   *   Unpacked operation.
   */
  public static function unpack(array $packed_operation): OrderOperationInterface {
    [$type, $args] = $packed_operation;
    $class = static::KNOWN_CLASSES[$type]
      ?? throw new \InvalidArgumentException('Unsupported order operation type ' . $type);
    return new $class(...$args);
  }

}
