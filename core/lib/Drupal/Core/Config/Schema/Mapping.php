<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Defines a mapping configuration element.
 *
 * This object may contain any number and type of nested properties and each
 * property key may have its own definition in the 'mapping' property of the
 * configuration schema.
 *
 * Properties in the configuration value that are not defined in the mapping
 * will get the 'undefined' data type.
 *
 * Read https://www.drupal.org/node/1905070 for more details about configuration
 * schema, types and type resolution.
 */
class Mapping extends ArrayElement {

  /**
   * {@inheritdoc}
   */
  protected function getElementDefinition($key) {
    $value = $this->value[$key] ?? NULL;
    $definition = $this->definition['mapping'][$key] ?? [];
    return $this->buildDataDefinition($definition, $value, $key);
  }

  /**
   * Gets all valid keys in this mapping.
   *
   * @return string[]
   *   A list of valid keys given the values in this mapping.
   */
  public function getValidKeys(): array {
    $all_keys = $this->getLocallyDefinedKeys() + $this->getInheritedKeys();
    return array_keys($all_keys);
  }

  /**
   * Gets all required keys in this mapping.
   *
   * @return string[]
   *   A list of required keys given the values in this mapping.
   */
  public function getRequiredKeys(): array {
    $all_keys = $this->getLocallyDefinedKeys() + $this->getInheritedKeys();
    $required_keys = array_filter(
      $all_keys,
      fn (array $raw_schema_definition) => !array_key_exists('requiredKey', $raw_schema_definition),
    );
    return array_keys($required_keys);
  }

  /**
   * Gets the mapping keys defined locally.
   *
   * @return array
   *   Raw schema definitions: keys are mapping keys, values are their
   *   definitions.
   */
  protected function getLocallyDefinedKeys(): array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition && self::validateMappingConfigSchemaDefinition($definition));
    // f.e. when using `type: mapping`, no keys have been defined, but it's
    // still possible to define keys under `mapping: {…}`.
    return $definition->toArray()['mapping'];
  }

  /**
   * Gets the mapping keys defined in the schema, including inheritance.
   *
   * TRICKY: $this->getDataDefinition() returns only the subset of the
   * definition based on the values that are present
   * TRICKY: $this->getTypedDataManager()->getDefinition() performs inheritance
   * for us, but ignores the locally defined keys.
   *
   * @return array
   *   Raw schema definitions: keys are mapping keys, values are their
   *   definitions.
   *
   * @see \Drupal\Core\Config\TypedConfigManager::getDefinitionWithReplacements()
   */
  protected function getInheritedKeys(): array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition);
    $config_schema_definition = $this->getTypedDataManager()->getDefinition($definition->getDataType());
    // f.e. when using `type: _core_config_info`, which extends `type: mapping`,
    // we need the keys defined for `type: _core_config_info` to be inherited.
    return $config_schema_definition['mapping'];
  }

  /**
   * Gets all optional keys in this mapping.
   *
   * @return string[]
   *   A list of optional keys given the values in this mapping.
   */
  public function getOptionalKeys(): array {
    return array_diff($this->getValidKeys(), $this->getRequiredKeys());
  }

  /**
   * Validates optional `requiredKey` flags in mappings.
   *
   * Validates that the values for either optional flag are correct. Does not
   * validate the semantics, only the shapes.
   *
   * @param \Drupal\Core\TypedData\MapDataDefinition $definition
   *   The config schema definition for a `type: mapping`.
   *
   * @return bool
   */
  protected static function validateMappingConfigSchemaDefinition(MapDataDefinition $definition): bool {
    $definition = $definition->toArray();
    assert(array_key_exists('mapping', $definition));

    // Validates `requiredKey` flag in mapping definitions.
    foreach ($definition['mapping'] as $options) {
      if (!array_key_exists('requiredKey', $options)) {
        // This flag is optional.
        continue;
      }
      if ($options['requiredKey'] !== FALSE) {
        throw new \LogicException('The `requiredKey` flag must either be omitted or have `false` as the value.');
      }
    }

    return TRUE;
  }

}
