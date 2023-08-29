<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\TypedData\DataDefinitionInterface;
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
   * Resolves a `type: mapping` instance to its data definitions.
   *
   * Resolves (dynamic) subtypes of `type: mapping`, to determine the exact data
   * definitions for each key in the mapping, as prescribed by config schema.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   The data definition for each of the keys.
   */
  public function getResolvedDataDefinitions() : array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition);
    assert(self::validateMappingConfigSchemaDefinition($definition));

    // TRICKY: This cannot use Mapping::getElements() because it only considers
    // keys that are present, making that impossible to use detecting missing
    // keys.
    // @todo Consider adding ArrayElement::getSchemaElements() that does not
    // look at the keys that are present, but the keys that are defined in the
    // schema. That would allow this to be removed.
    $resolved_mapping = [];
    foreach (array_keys($definition['mapping']) as $key) {
      $resolved_mapping[$key] = $this->getElementDefinition($key);
    }
    assert(self::isArrayOfDataDefinitions($resolved_mapping));
    return $resolved_mapping;
  }

  /**
   * Gets all valid keys in this mapping.
   *
   * @return string[]
   *   A list of valid keys given the values in this mapping.
   */
  public function getValidKeys(): array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition && self::validateMappingConfigSchemaDefinition($definition));
    return array_keys($definition->toArray()['mapping']);
  }

  /**
   * Gets all required keys in this mapping.
   *
   * @return string[]
   *   A list of required keys given the values in this mapping.
   */
  public function getRequiredKeys(): array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition && self::validateMappingConfigSchemaDefinition($definition));

    $required_keys = array_keys(array_filter(
      $this->getResolvedDataDefinitions(),
      [__CLASS__, 'isRequiredMappingKey']
    ));

    assert(empty(array_diff($required_keys, $this->getValidKeys())), 'Required keys must also be valid keys.');
    return $required_keys;
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
   * Gets all conditionally optional keys across all possible types for each.
   *
   * @return string[]
   *   A list of conditionally optional keys.
   */
  public function getConditionallyOptionalKeys(): array {
    if (empty($this->getValidKeys())) {
      return [];
    }

    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition && self::validateMappingConfigSchemaDefinition($definition));

    // The original mapping definition is used to determine the original types.
    // (This contains the raw definitions for types, as in `*.schema.yml`.)
    $original_mapping_definition = $definition->toArray()['mapping'];
    // The resolved mapping definition is used to determine the resolved types.
    // (This contains the resolved definitions, after resolving dynamic types.)
    $resolved_mapping_definition = $this->getResolvedDataDefinitions();
    assert(count($original_mapping_definition) === count($resolved_mapping_definition));

    // This is complex, so assertions help understand what is happening. The
    // original mapping definition is an array of arrays, the resolved one is an
    // array of data definitions. But they are equally complete: they have the
    // same keys, just not the same values.
    assert(Inspector::assertAllArrays($original_mapping_definition));
    assert(!self::isArrayOfDataDefinitions($original_mapping_definition));
    assert(self::isArrayOfDataDefinitions($resolved_mapping_definition));
    assert([] === array_diff_key($original_mapping_definition, $resolved_mapping_definition));

    // Statically typed keys are those whose type DID NOT change after resolving
    // replacements in the `type`. Dynamically typed keys are the opposite.
    // For example:
    // - static: `type: something.foo`
    // - dynamic: `type: something.foo_[%parent.type]`
    // @see \Drupal\Core\Config\TypedConfigManager::buildDataDefinition()
    // @see \Drupal\Core\Config\TypedConfigManager::getDefinitionWithReplacements()
    $dynamically_typed_keys = array_filter(
      $resolved_mapping_definition,
      fn (DataDefinitionInterface $resolved_definition, string $key) => $resolved_definition->getDataType() !== $original_mapping_definition[$key]['type'],
      ARRAY_FILTER_USE_BOTH
    );

    // Statically typed keys cannot have conditionally optional keys.
    if (empty($dynamically_typed_keys)) {
      return [];
    }

    // Determine which (if any) of the dynamically typed keys have conditionally
    // optional keys.
    $conditionally_optional_keys = [];
    $all_type_definitions = $this->getTypedDataManager()->getDefinitions();
    foreach ($dynamically_typed_keys as $key => $resolved_element_definition) {
      $original_type = $definition['mapping'][$key]['type'];
      $resolved_type = $resolved_element_definition->toArray()['type'];
      assert($original_type !== $resolved_type, 'This is not a dynamic type.');

      // For each dynamic type, there must be >=1 possible types to resolve to.
      $possible_types = $this->getTypedDataManager()->getPossibleTypes($original_type);
      $possible_type_definitions = array_intersect_key($all_type_definitions, array_fill_keys($possible_types, TRUE));

      // Determine which of these types have an optional key.
      $possible_types_optional_key = array_filter(
        $possible_type_definitions,
        fn (array $raw_def) => array_key_exists('requiredKey', $raw_def)
      );

      // Conditionally optional keys are those that are optional some of
      // time: some of the types have an optional key, but not all.
      if (!empty($possible_types_optional_key) && count($possible_types_optional_key) < count($possible_type_definitions)) {
        $conditionally_optional_keys[] = $key;
      }
    }
    return $conditionally_optional_keys;
  }

  /**
   * Gets all conditionally required keys across all possible types for each.
   *
   * Alias for ::getConditionallyOptionalKeys().
   *
   * @return string[]
   *   A list of conditionally required keys.
   */
  public function getConditionallyRequiredKeys(): array {
    return $this->getConditionallyOptionalKeys();
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

  /**
   * Checks whether the specified data definition for a mapping key is required.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition to evaluate.
   *
   * @return bool
   *   Whether the `requiredKey` property is set or not.
   */
  protected static function isRequiredMappingKey(DataDefinitionInterface $definition): bool {
    return !array_key_exists('requiredKey', $definition->toArray());
  }

  /**
   * Asserts argument is an array containing DataDefinitionInterface instances.
   *
   * @param array $array
   *   Variable to be examined.
   *
   * @return bool
   *   TRUE if $array contains only DataDefinitionInterface instances.
   */
  protected static function isArrayOfDataDefinitions(array $array): bool {
    foreach ($array as $value) {
      if (!$value instanceof DataDefinitionInterface) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
