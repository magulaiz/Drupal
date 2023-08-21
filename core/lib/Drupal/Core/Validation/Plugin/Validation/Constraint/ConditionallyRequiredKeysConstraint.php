<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\TypedData\MapDataDefinition;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Checks that conditionally required keys of a mapping are present or absent.
 *
 * @Constraint(
 *   id = "ConditionallyRequiredKeys",
 *   label = @Translation("Conditionally required mapping keys", context = "Validation"),
 * )
 */
class ConditionallyRequiredKeysConstraint extends Constraint {

  /**
   * The error message if a key is missing.
   *
   * @var string
   */
  public string $message = "'@key' is a conditionally required key.";

  /**
   * The error message if a key is extraneous.
   *
   * @var string
   */
  public string $extraneousMessage = "'@key' is an extraneous key.";

  /**
   * The condition.
   *
   * Two key-value pairs:
   * - property (string): the path to a property whose value should be checked
   * - value (string): the value it must have for the listed keys to be required
   *
   * @var array
   */
  public array $condition;

  /**
   * Keys which are conditionally required.
   *
   * @var string[]
   */
  public array $keys;

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['condition', 'keys'];
  }

  /**
   * Validates the constraint options are valid for this mappings.
   *
   * Validates that the values for either optional flag are correct. Does not
   * validate the semantics, only the shapes.
   *
   * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
   *   The current execution context.
   *
   * @return void
   */
  public function validateOptions(ExecutionContextInterface $context): void {
    $mapping = $context->getObject();
    assert($mapping instanceof Mapping);
    $data_definition = $mapping->getDataDefinition();
    assert($data_definition instanceof MapDataDefinition);
    $definition = $data_definition->toArray();
    assert(array_key_exists('mapping', $definition));

    $property_path = sprintf(
      "%s:%s",
      $context->getRoot()->getDataDefinition()->getDataType(),
      $context->getPropertyPath(),
    );

    // Validate the `condition` option.
    if (!array_key_exists('key', $this->condition) || !array_key_exists('value', $this->condition)) {
      throw new \LogicException(sprintf(
        'The `condition` option for the mapping at %s is invalid. It must contain two key-value pairs: `key` containing a key in this mapping and `value` containing the value required at that key for the keys in `keys` to be required.',
        $property_path,
      ));
    }
    $condition_key = $this->condition['key'];
    if (array_key_exists('requiredKey', $definition['mapping'][$condition_key])) {
      throw new \LogicException(sprintf('The `condition.key` option for the mapping at %s is invalid. It must refer to a required key, but `%s` is an optional key.',
        $property_path,
        "$property_path.$condition_key",
      ));
    }

    // Validate the `keys` option.
    if (empty($this->keys)) {
      throw new \LogicException(sprintf("No conditionally required keys specified for %s.", $property_path));
    }
    foreach ($this->keys as $key) {
      if (!array_key_exists($key, $definition['mapping'])) {
        throw new \LogicException(sprintf(
          'The conditionally required key `%s` does not exist in the mapping at %s. The following keys exist: `%s`.',
          $key,
          $property_path,
          implode('`, `', array_keys($definition['mapping']))
        ));
      }

      if (!array_key_exists('requiredKey', $definition['mapping'][$key])) {
        throw new \LogicException(sprintf(
          'The conditionally required key `%s` exists in the mapping at %s but does not have `requiredKey: false` set. Add this, otherwise it cannot correctly behave as a conditionally required key.',
          $key,
          $property_path
        ));
      }
    }
  }

}
