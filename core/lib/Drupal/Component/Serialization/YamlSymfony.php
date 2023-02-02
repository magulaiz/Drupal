<?php

namespace Drupal\Component\Serialization;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Default serialization for YAML using the Symfony component.
 */
class YamlSymfony implements TaggedSerializationInterface {

  use TaggedSerializationTrait;

  /**
   * {@inheritdoc}
   */
  public static function encode($data) {
    try {
      // Set the indentation to 2 to match Drupal's coding standards.
      $yaml = new Dumper(2);
      return $yaml->dump($data, PHP_INT_MAX, 0, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE | SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
    catch (\Exception $e) {
      throw new InvalidDataTypeException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function decode($raw) {
    try {
      $yaml = new Parser();
      // Make sure we have a single trailing newline. A very simple config like
      // 'foo: bar' with no newline will fail to parse otherwise.
      $data = $yaml->parse($raw, SymfonyYaml::PARSE_EXCEPTION_ON_INVALID_TYPE | SymfonyYaml::PARSE_CUSTOM_TAGS);

      $is_array = is_array($data);
      if (!$is_array) {
        $data = [$data];
      }

      // Support Symfony 3.3 TaggedValue objects.
      array_walk_recursive($data, function (&$item) {
        if ($item instanceof TaggedValue) {
          $item = static::executeTagCallback($item->getValue(), $item->getTag());
        }
      });

      return $is_array ? $data : reset($data);
    }
    catch (\Exception $e) {
      throw new InvalidDataTypeException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getFileExtension() {
    return 'yml';
  }

}
