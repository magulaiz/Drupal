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

}
