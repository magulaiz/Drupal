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
   * Keys inherited from a parent mapping, but are optional in this subtype.
   *
   * @var array
   */
  public array $markInheritedKeysAsOptional = [];

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
      throw new \DomainException();
    }

    // Important! This infers keys from the config schema definition, not the
    // provided data.
    $inferred_keys = static::inferKeys($context->getObject());

    // Exclude inherited required keys explicitly marked as optional.
    assert(
      array_diff($this->markInheritedKeysAsOptional, $inferred_keys) == [],
      sprintf(
        "Some inherited keys that is marked as optional are already optional in the parent type: %s.\nKeys marked as optional: %s. \nInferred required keys: %s.\n",
        implode(', ', array_diff($this->markInheritedKeysAsOptional, $inferred_keys)),
        implode(', ', $this->markInheritedKeysAsOptional),
        implode(', ', $inferred_keys)
      )
    );
    return array_diff($inferred_keys, $this->markInheritedKeysAsOptional);
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

    $definition = $definition->toArray();
    assert(array_key_exists('mapping', $definition));

    // Mapping keys are required by default, unless they have a `requiredKey`
    // property that is set to `false`
    $required_keys = array_filter(
      $definition['mapping'],
      fn (array $value, string $key) => !array_key_exists('requiredKey', $value) || $value['requiredKey'] === TRUE,
      ARRAY_FILTER_USE_BOTH
    );

    return array_keys($required_keys);
  }

}
