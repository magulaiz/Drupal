<?php

namespace Drupal\serialization\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\YamlEncoder as BaseYamlEncoder;

/**
 * Adds YAML support for serializer.
 *
 * This acts as a wrapper class for Symfony's YamlEncoder so that it is not
 * implementing NormalizationAwareInterface, and can be normalized externally.
 *
 * @internal
 *   This encoder should not be used directly. Rather, use the `serializer`
 *   service.
 */
class YamlEncoder extends BaseYamlEncoder implements EncoderInterface, DecoderInterface {

  /**
   * The formats that this Encoder supports.
   *
   * @var array
   */
  protected static $format = ['yaml'];

  /**
   * An instance of the Symfony YamlEncoder to perform the actual encoding.
   *
   * @var \Symfony\Component\Serializer\Encoder\YamlEncoder
   */
  protected $baseEncoder;

  /**
   * Gets the base encoder instance.
   *
   * @return \Symfony\Component\Serializer\Encoder\YamlEncoder
   *   The base encoder.
   */
  public function getBaseEncoder() {
    if (!isset($this->baseEncoder)) {
      $this->baseEncoder = new BaseYamlEncoder();
    }

    return $this->baseEncoder;
  }

  /**
   * Sets the base encoder instance.
   *
   * @param \Symfony\Component\Serializer\Encoder\YamlEncoder $encoder
   *   The encoder instance.
   */
  public function setBaseEncoder($encoder) {
    $this->baseEncoder = $encoder;
  }

  /**
   * {@inheritdoc}
   */
  public function encode(mixed $data, string $format, array $context = []): string {
    $context = array_merge([
      'yaml_inline' => PHP_INT_MAX,
    ], $context);
    return $this->getBaseEncoder()->encode($data, $format, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding(string $format): bool {
    return in_array($format, static::$format);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($data, $format, array $context = []): mixed {
    return $this->getBaseEncoder()->decode($data, $format, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding(string $format): bool {
    return in_array($format, static::$format);
  }

}
