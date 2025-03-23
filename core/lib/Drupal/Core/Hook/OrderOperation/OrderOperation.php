<?php

declare(strict_types = 1);

namespace Drupal\Core\Hook\OrderOperation;

/**
 * Base class for order operations.
 */
abstract class OrderOperation {

  const array KNOWN_CLASSES = [
    FirstOrLast::class,
    BeforeOrAfterModule::class,
    BeforeOrAfterIdentifier::class,
  ];

  /**
   * Serializes an order operation object.
   *
   * @return array
   *   Packed operation.
   */
  final public function pack(): array {
    $type_index = array_search(get_class($this), self::KNOWN_CLASSES);
    if ($type_index === FALSE) {
      throw new \LogicException(sprintf('Unknown subclass %s of internal class %s.', static::class, self::class));
    }
    return [$type_index, get_object_vars($this)];
  }

  /**
   * Unserializes an order operation object.
   *
   * @param array $packed_operation
   *   Packed operation.
   *
   * @return self
   *   Unpacked operation.
   */
  final public static function unpack(array $packed_operation): self {
    [$type_index, $args] = $packed_operation;
    $class = static::KNOWN_CLASSES[$type_index]
      ?? throw new \InvalidArgumentException('Unsupported order operation type index ' . $type_index);
    return new $class(...$args);
  }

}
