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
    $all_keys = $this->getDefinedKeys();
    return array_keys($all_keys);
  }

  /**
   * Gets all required keys in this mapping.
   *
   * Keys are required by default, they can opt out by specifying
   * `requiredKey: false`. Deprecated keys are also treated as optional.
   *
   * @return string[]
   *   A list of required keys given the values in this mapping.
   */
  public function getRequiredKeys(): array {
    $all_keys = $this->getDefinedKeys();
    $required_keys = array_filter(
      $all_keys,
      fn (array $raw_schema_definition) => !array_key_exists('requiredKey', $raw_schema_definition) && !array_key_exists('deprecated', $raw_schema_definition),
    );
    return array_keys($required_keys);
  }

  /**
   * Gets the keys defined for this mapping (locally defined + inherited).
   *
   * @return array
   *   Raw schema definitions: keys are mapping keys, values are their
   *   definitions.
   */
  protected function getDefinedKeys(): array {
    $definition = $this->getDataDefinition();
    assert($definition instanceof MapDataDefinition && self::validateMappingConfigSchemaDefinition($definition));
    // f.e. when using `type: mapping`, no keys have been defined, but it's
    // still possible to define keys under `mapping: {…}`.
    return $definition->toArray()['mapping'];
  }

  /**
   * Gets all dynamically valid keys.
   *
   * When the `type` of the mapping is dynamic itself, such as:
   * - `type: editor.settings.[%parent.editor]`
   * - `type: field.field_settings.[%parent.field_type]`
   * - `type: editor.image_upload_settings.[status]`
   * then the mapping at this property path may have keys that are dynamically
   * valid. This means that depending on other values (hence "dynamically"),
   * different sets of keys are considered valid.
   * For example: the settings associated with a FieldConfig depends on which
   * field plugin that field uses.
   *
   * @return string[][]
   *   A list of dynamically valid keys. An array with:
   *   - a key for every possible resolved type
   *   - the corresponding value an array of the additional mapping keys that
   *     are supported for this resolved type
   *
   * @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
   */
  public function getDynamicallyValidKeys(): array {
    $parent_data_def = $this->getParent()?->getDataDefinition();
    if ($parent_data_def === NULL) {
      return [];
    }

    // The parent mapping definition is used to determine the type used to
    // determine the type of this mapping.
    // f.e.:
    // 1. `type: editor.settings.[%parent.editor]`
    // 2. `type: editor.image_upload_settings.[status]`.
    $original_mapping_type = match (TRUE) {
      $parent_data_def instanceof MapDataDefinition => $parent_data_def->toArray()['mapping'][$this->getName()]['type'],
      $parent_data_def instanceof SequenceDataDefinition => $parent_data_def->toArray()['sequence']['type'],
      default => throw new \LogicException('Invalid config schema detected.'),
    };

    // If the original mapping type is not dynamic, there's no additional work.
    if (!str_contains($original_mapping_type, ']')) {
      return [];
    }

    // When using dynamic typing, the type names contain variable values. These
    // refer to nested configuration keys (that will be replaced by their value)
    // or the special strings '%key', '%parent' or '%type'.
    // When only those special strings are used, then no dynamism exists: only
    // if a non-special string is used, will any other configuration key's value
    // actually be used to determine the type.
    // Explained by examples:
    // - CKEditor 5 uses 'ckeditor5.plugin.[%key]', but that uses no information
    //   stored elsewhere: the chosen key (in a sequence) determines the type.
    // - third party settings use '[%parent.%parent.%type].third_party.[%key]',
    //   but the first variable value only causes the config entity type (at the
    //   root) to be inherited (f.e. 'node.type.third_party.[%key]').
    // - field instances 'field.value.[%parent.%parent.field_type]', which is
    //   used to determine the type of the default field value, to ensure
    //   matches the precise config schema type of the field type
    // - views use 'views.filter.[plugin_id]' to allow the value itself to
    //   determine which filter plugin it  uses, in the 'plugin_id' key.
    // Note that only the last two examples contained strings other than the 3
    // special ones.
    // @see \Drupal\Core\Config\TypedConfigManager::replaceName()
    $matches = [];
    preg_match_all("/\[(.*)\]/U", $original_mapping_type, $matches);
    $variable_values = array_merge(...array_map(
      fn (string $s) => explode('.', $s),
      $matches[1]
    ));
    if (empty(array_diff($variable_values, ['%key', '%parent', '%type']))) {
      return [];
    }

    // Find all possible types for the given original mapping type.
    // f.e.:
    // 1. `editor.settings.unicorn` or `editor.settings.trex`
    // 2. `editor.image_upload_settings.*` or `editor.image_upload_settings.1`
    $possible_types = $this->getPossibleTypes($original_mapping_type);

    // TRICKY: it is tempting to not consider this a dynamic type if only one
    // concrete type is installed. But this would lead to different validation
    // errors when modules are installed or uninstalled.
    assert(!empty($possible_types));

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

    // From all valid keys across all types, get the ones for the fallback type:
    // the keys in this mapping definition are inherited by all type definitions
    // and are hence valid everywhere. Not all types have a fallback type.
    // @see \Drupal\Core\Config\TypedConfigManager::getDefinitionWithReplacements()
    $fallback_type = $this->getTypedDataManager()->findFallback($original_mapping_type);
    $valid_keys_everywhere = array_intersect_key(
      $valid_keys_per_type,
      [$fallback_type => NULL],
    );
    assert(count($valid_keys_everywhere) <= 1);
    $statically_required_keys = NestedArray::mergeDeepArray($valid_keys_everywhere);

    // Now that statically valid keys are known, determine which valid keys are
    // only valid in some cases: filter away the statically valid keys that are
    // present in each per-type array of valid keys.
    $valid_keys_some = array_diff_key($valid_keys_per_type, $valid_keys_everywhere);
    $valid_keys_some_processed = array_map(
      fn (array $keys) => array_values(array_filter($keys, fn (string $key) => !in_array($key, $statically_required_keys, TRUE))),
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
    return array_values(array_diff($this->getValidKeys(), $this->getRequiredKeys()));
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
   * Returns all possible types for the type with the given name.
   *
   * @param string $name
   *   Configuration name or key.
   *
   * @return string[]
   *   All possible types for a given type. For example,
   *   `core_date_format_pattern.[%parent.locked]` will return:
   *   - `core_date_format_pattern.0`
   *   - `core_date_format_pattern.1`
   *   If a fallback name is available, that will be returned too. In this
   *   example, that would be `core_date_format_pattern.*`.
   */
  protected function getPossibleTypes(string $name): array {
    // First, parse from e.g.
    // `module.something.foo_[%parent.locked]`
    // this:
    // `[%parent.locked]`
    // or from
    // `[%parent.%parent.%type].third_party.[%key]`
    // this:
    // `[%parent.%parent.%type]` and `[%key]`.
    // And collapse all these to just `[]`.
    // @see \Drupal\Core\Config\TypedConfigManager::replaceVariable()
    $matches = [];
    if (preg_match_all('/(\[[^\]]+\])/', $name, $matches) >= 1) {
      $name = str_replace($matches[0], '[]', $name);
    }
    // Then, replace all `[]` occurrences with `.*` and escape all periods for
    // use in a regex. So:
    // `module\.something\.foo_.*`
    // or
    // `.*\.third_party\..*`
    $regex = str_replace(['.', '[]'], ['\.', '.*'], $name);
    // Now find all possible types:
    // 1. `module.something.foo_foo`, `module.something.foo_bar`, etc.
    $possible_types = array_filter(
      array_keys($this->getTypedDataManager()->getDefinitions()),
      fn (string $type) => preg_match("/^$regex$/", $type) === 1
    );
    // 2. The fallback: `module.something.*` — if no concrete definition for it
    // exists.
    $fallback_type = $this->getTypedDataManager()->findFallback($name);
    if ($fallback_type && !in_array($fallback_type, $possible_types, TRUE)) {
      $possible_types[] = $fallback_type;
    }
    return $possible_types;
  }

}
