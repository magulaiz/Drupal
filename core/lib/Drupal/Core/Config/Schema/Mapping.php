<?php

namespace Drupal\Core\Config\Schema;

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
   *
   * @todo make protected
   */
  public static function isRequiredMappingKey(DataDefinitionInterface $definition): bool {
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
   *
   * @todo make protected
   */
  public static function isArrayOfDataDefinitions(array $array): bool {
    foreach ($array as $value) {
      if (!$value instanceof DataDefinitionInterface) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
