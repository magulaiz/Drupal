<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Component\Utility\NestedArray;
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
   * Gets all conditionally valid keys.
   *
   * (When the `type` of the mapping is dynamic itself.)
   *
   * @return string[]
   *   A list of conditionally optional keys. An array with:
   *   - a key for every possible resolved type
   *   - the corresponding value an array of the additional mapping keys that
   *     are supported for this resolved type
   */
  public function getConditionallyValidKeys(): array {
    if ($this->getParent() === NULL) {
      return [];
    }

    // The original mapping definition is used to determine the original type.
    // f.e.:
    // 1. `type: editor.settings.[%parent.editor]`
    // 2. `type: editor.image_upload_settings.[status]`.
    $parent_data_def = $this->getParent()->getDataDefinition();
    $original_mapping_type = match (TRUE) {
      $parent_data_def instanceof MapDataDefinition => $parent_data_def->toArray()['mapping'][$this->getName()]['type'],
      $parent_data_def instanceof SequenceDataDefinition => $parent_data_def->toArray()['sequence']['type'],
      default => throw new \LogicException('Invalid config schema detected.'),
    };

    // If the original mapping type is not dynamic, there's no additional work.
    if (strpos($original_mapping_type, ']') === FALSE) {
      return [];
    }

    // Find all possible types for the given original mapping type.
    // f.e.:
    // 1. `editor.settings.unicorn` or `editor.settings.trex`
    // 2. `editor.image_upload_settings.*` or `editor.image_upload_settings.1`
    $possible_types = $this->getTypedDataManager()->getPossibleTypes($original_mapping_type);

    // This used a dynamic type, but only one concrete type is installed.
    if (count($possible_types) <= 1) {
      return [];
    }

    // Determine all valid keys across all possible types.
    $all_type_definitions = $this->getTypedDataManager()->getDefinitions();
    $possible_type_definitions = array_intersect_key($all_type_definitions, array_fill_keys($possible_types, TRUE));
    // TRICKY: \Drupal\Core\Config\TypedConfigManager::getDefinition() does the
    // necessary resolving, but TypedConfigManager::getDefinitions() does not! 🤷‍♂️
    // @see \Drupal\Core\Config\TypedConfigManager::getDefinitionWithReplacements()
    // @see ::getValidKeys()
    $valid_keys_per_type = array_map(
      fn (string $possible_type) => array_keys($this->getTypedDataManager()->getDefinition($possible_type)['mapping'] ?? []),
      // Keep the original types, but array_map() does not allow using the keys,
      // so pass the same information twice: this logic will replace the values.
      array_combine(
        array_keys($possible_type_definitions),
        array_keys($possible_type_definitions),
      ),
    );

    // From all valid keys, determine which ones are supported everywhere:
    // inspect the fallback type — if it exists.
    // @todo use \Drupal\Core\Config\TypedConfigManager::getFallbackName()?
    $valid_keys_everywhere = array_filter(
      $valid_keys_per_type,
      fn (array $valid_keys, string $type) => str_ends_with($type, '.*'),
      ARRAY_FILTER_USE_BOTH
    );
    // There can only be one fallback type whose definition is inherited by all
    // children.
    // @see \Drupal\Core\Config\TypedConfigManager::getDefinitionWithReplacements()
    // assert(count($valid_keys_all) <= 1);
    if (count($valid_keys_everywhere) > 1) {
      // @todo BROKEN! Fix this in \Drupal\Core\Config\TypedConfigManager::getPossibleTypes()
    }
    $unconditional_keys = NestedArray::mergeDeepArray($valid_keys_everywhere);

    // Now that unconditionally valid keys are known, determine which valid keys
    // are only valid in some cases: filter away the unconditional keys that are
    // present in each per-type array of valid keys.
    $valid_keys_some = array_diff_key($valid_keys_per_type, $valid_keys_everywhere);
    $valid_keys_some_processed = array_map(
      fn (array $keys) => array_filter($keys, fn (string $key) => !in_array($key, $unconditional_keys, TRUE)),
      $valid_keys_some
    );
    return $valid_keys_some_processed;
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
