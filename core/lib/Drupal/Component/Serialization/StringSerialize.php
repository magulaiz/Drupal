<?php

namespace Drupal\Component\Serialization;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;

/**
 * Data is serialized as string.
 */
class StringSerialize implements SerializationInterface {

  /**
   * {@inheritdoc}
   */
  public static function encode($data): string {
    if (is_object($data) && method_exists($data, '__toString')) {
      return $data->__toString();
    }
    elseif (is_scalar($data)) {
      return (string) $data;
    }
    throw new InvalidDataTypeException('Cannot converted data to string');
  }

  /**
   * {@inheritdoc}
   */
  public static function decode($raw) {
    return $raw;
  }

  /**
   * {@inheritdoc}
   */
  public static function getFileExtension(): string {
    return 'string';
  }

}
