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
   * The error message if a key is missing.
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
   * Validates optional `requiredKey` flags in mappings.
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
    // property.
    $required_keys = array_filter(
      $definition['mapping'],
      fn (array $value, string $key) => !array_key_exists('requiredKey', $value),
      ARRAY_FILTER_USE_BOTH
    );

    return array_keys($required_keys);
  }

}
