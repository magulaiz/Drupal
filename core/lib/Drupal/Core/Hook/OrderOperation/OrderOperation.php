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
    AbsoluteOrderOperation::class,
    RelativeOrderOperation::class,
  ];

  /**
   * Serializes an order operation object.
   *
   * @param \Drupal\Core\Hook\OrderOperation\OrderOperationInterface $operation
   *   Order operation object.
   *
   * @return string
   *   Serialized object.
   */
  public static function pack(OrderOperationInterface $operation): string {
    if (!in_array(get_class($operation), static::KNOWN_CLASSES)) {
      throw new \InvalidArgumentException('Unsupported order operation class ' . get_class($operation));
    }
    return serialize($operation);
  }

  /**
   * Unserializes an order operation object.
   *
   * @param string $serialized_operation
   *   Serialized operation.
   *
   * @return \Drupal\Core\Hook\OrderOperation\OrderOperationInterface
   *   Unserialized operation.
   */
  public static function unpack(string $serialized_operation): OrderOperationInterface {
    return unserialize($serialized_operation, ['allowed_classes' => static::KNOWN_CLASSES]);
  }

}
