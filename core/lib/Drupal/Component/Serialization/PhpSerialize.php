<?php

declare(strict_types=1);

namespace Drupal\Component\Serialization;

/**
 * Default serialization for serialized PHP.
 */
class PhpSerialize implements ObjectAwareSerializationInterface {

  /**
   * {@inheritdoc}
   */
  public static function encode($data) {
    return serialize($data);
  }

  /**
   * {@inheritdoc}
   */
  public static function decode($raw) {
    return unserialize($raw);
  }

  /**
   * {@inheritdoc}
   */
  public static function getFileExtension() {
    return 'serialized';
  }

}
