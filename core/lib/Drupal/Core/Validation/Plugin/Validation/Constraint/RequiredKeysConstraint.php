<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\TypedData\MapDataDefinition;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Checks that all the required keys of a mapping are present.
 *
 * @Constraint(
 *   id = "RequiredKeys",
 *   label = @Translation("Required mapping keys", context = "Validation"),
 * )
 */
class RequiredKeysConstraint extends Constraint {

  /**
   * The error message if an key is missing.
   *
   * @var string
   */
  public string $message = "'@key' is a required key.";

  /**
   * Keys which are required — only `<infer>` supported currently.
   *
   * @var string
   */
  public string $requiredKeys;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'requiredKeys';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['requiredKeys'];
  }

  /**
   * Returns the list of required keys.
   *
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   *   The current execution context.
   *
   * @return string[]
   *   The keys that will be considered valid.
   */
  public function getRequiredKeys(ExecutionContextInterface $context): array {
    // The only value currently supported is the string `<infer>`.
    if ($this->requiredKeys !== '<infer>') {
      throw new \DomainException("Only '<infer>' is allowed.");
    }

    // Important! This infers keys from the config schema definition, not the
    // provided data.
    return static::inferKeys($context->getObject());
  }

  /**
   * Validates optional `requiredKey` and `requiredKeyIf` flags in mappings.
   *
   * Validates that the values for either optional flag are correct. Does not
   * validate the semantics, only the shapes.
   *
   * @param \Drupal\Core\TypedData\MapDataDefinition $definition
   *   The config schema definition for a `type: mapping`.
   *
   * @return void
   */
  protected static function validateMappingConfigSchemaDefinition(MapDataDefinition $definition): void {
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

    // Validates `requiredKeyIf` flag in mapping definitions.
    foreach ($definition['mapping'] as $options) {
      if (!array_key_exists('requiredKeyIf', $options)) {
        // This flag is optional.
        continue;
      }

      // Require fully defined conditional required keys.
      if (!array_key_exists('path', $options['requiredKeyIf']) || !array_key_exists('value', $options['requiredKeyIf'])) {
        throw new \LogicException('`requiredKeyIf` must contain two key-value pairs: `path` containing a property path string and `value` containing the value required at that property path for this key to be required.');
      }
    }
  }

  /**
   * Tries to auto-detect the schema-defined keys in a mapping.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $mapping
   *   The mapping to inspect.
   *
   * @return string[]
   *   The keys defined in the mapping's schema.
   */
  protected static function inferKeys(Mapping $mapping): array {
    $definition = $mapping->getDataDefinition();
    assert($definition instanceof MapDataDefinition);

    self::validateMappingConfigSchemaDefinition($definition);

    $definition = $definition->toArray();
    assert(array_key_exists('mapping', $definition));

    // Mapping keys are required by default, unless they have a `requiredKey`
    // or `requiredKeyIf` property.
    $required_keys = array_filter(
      $definition['mapping'],
      fn (array $value, string $key) => !array_key_exists('requiredKey', $value) && !array_key_exists('requiredKeyIf', $value),
      ARRAY_FILTER_USE_BOTH
    );

    $conditionally_required_keys = array_filter(
      $definition['mapping'],
      fn (array $value, string $key) => array_key_exists('requiredKeyIf', $value) && is_array($value['requiredKeyIf']),
      ARRAY_FILTER_USE_BOTH
    );

    // Validate.
    foreach ($conditionally_required_keys as $key => $definition) {
      $path = $definition['requiredKeyIf']['path'];

      // Forbid conditionally required keys from depending on anything else than
      // unconditionally required keys: not on optional keys nor
      // conditionally required keys.
      if (!array_key_exists($path, $required_keys)) {
        throw new \LogicException(sprintf('Conditionally required keys must depend only on unconditionally required keys. The dependency of `%s` on `%s` violates this.',
          $mapping->getPropertyPath() . ":$key",
          $mapping->getPropertyPath() . ":$path",
        ));
      }
    }

    // Evaluate which conditionally required keys are actually required for the
    // provided data.
    foreach ($conditionally_required_keys as $key => $definition) {
      $path = $definition['requiredKeyIf']['path'];
      $required_value = $definition['requiredKeyIf']['value'];
      try {
        if ($mapping->get($path)->getValue() !== $required_value) {
          unset($conditionally_required_keys[$key]);
        }
      }
      catch (\InvalidArgumentException) {
        // Even though conditionally required keys depend only on
        // unconditionally required keys (see earlier exception), it's still
        // possible that the data violates this requirement. The only possible
        // decision here is to treat this key as optional.
        unset($conditionally_required_keys[$key]);
      }
    }

    return array_keys($required_keys + $conditionally_required_keys);
  }

}
