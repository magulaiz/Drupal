<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\MapDataDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
class RequiredKeysConstraint extends Constraint implements ContainerFactoryPluginInterface {

  /**
   * The error message if a key is missing.
   *
   * @var string
   */
  public string $message = "'@key' is a required key.";

  /**
   * The error message if a conditionally required key is missing.
   *
   * @var string
   */
  public string $conditionalMessage = "'@key' is a conditionally required key.";

  /**
   * The error message if a key is extraneous.
   *
   * @var string
   */
  public string $extraneousMessage = "'@key' is an extraneous key.";

  /**
   * Keys which are required — only `<infer>` supported currently.
   *
   * @var string
   */
  public string $requiredKeys;

  /**
   * Constructs a RequiredKeysConstraint.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The plugin manager associated with the constraint.
   * @param mixed|null $options
   *   The options (as associative array) or the value for the default option
   *   (any other type).
   * @param array|null $groups
   *   An array of validation groups.
   * @param mixed|null $payload
   *   Domain-specific data attached to a constraint.
   */
  public function __construct(protected readonly TypedConfigManagerInterface $typedConfigManager, mixed $options = NULL, array $groups = NULL, mixed $payload = NULL) {
    parent::__construct($options, $groups, $payload);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.typed'),
      $configuration
    );
  }

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

    return $this->inferKeys($context->getObject());
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
   * Infers schema-defined keys in a mapping, resolving types using the value.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $mapping
   *   The mapping to inspect.
   *
   * @return string[][]
   *   The keys defined in the mapping's schema. Two subsets are identified:
   *   - `unconditional` contains all unconditionally required keys
   *   - `conditional` contains all conditionally required keys
   *   - 'extraneous' contains all conditionally optional keys
   *   Not returned: unconditionally optional keys.
   */
  protected function inferKeys(Mapping $mapping): array {
    $definition = $mapping->getDataDefinition();
    assert($definition instanceof MapDataDefinition);

    self::validateMappingConfigSchemaDefinition($definition);

    // The original mapping definition is used to determine the original types.
    // (This contains the raw definitions for types, as in `*.schema.yml`.)
    $original_mapping_definition = $definition->toArray()['mapping'];
    // The resolved mapping definition is used to determine the resolved types.
    // (This contains the resolved definitions, after resolving dynamic types.)
    $resolved_mapping_definition = $this->resolveMapping($mapping);
    assert(count($original_mapping_definition) === count($resolved_mapping_definition));

    // Some mappings are empty.
    if (empty($original_mapping_definition)) {
      return [
        'unconditional' => [],
        'conditional' => [],
        'extraneous' => [],
      ];
    }

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
    $statically_typed_keys = array_filter(
      $resolved_mapping_definition,
      fn (DataDefinitionInterface $resolved_definition, string $key) => $resolved_definition->getDataType() === $original_mapping_definition[$key]['type'],
      ARRAY_FILTER_USE_BOTH
    );
    $dynamically_typed_keys = array_diff_key($resolved_mapping_definition, $statically_typed_keys);

    // Assign each of $statically_typed_keys to one of 2 buckets.
    $unconditionally_required_keys = array_filter($statically_typed_keys, [__CLASS__, 'isRequiredMappingKey']);
    $unconditionally_optional_keys = array_diff_key($statically_typed_keys, $unconditionally_required_keys);

    // Assign each of $dynamically_typed_keys to one of 4 buckets.
    $dyn_typed_keys_unconditionally_required = [];
    $dyn_typed_keys_unconditionally_optional = [];
    $dyn_typed_keys_conditionally_required = [];
    $dyn_typed_keys_conditionally_optional = [];
    $all_type_definitions = $this->typedConfigManager->getDefinitions();
    foreach ($dynamically_typed_keys as $key => $resolved_element_definition) {
      $original_type = $definition['mapping'][$key]['type'];
      assert($original_type !== $resolved_element_definition->toArray()['type'], 'This is not a dynamic type.');

      // For each dynamic type, there must be >=1 possible types to resolve to.
      // To determine the conditionality of a key being required or not, it is
      // necessary to check if all possible types are:
      // 1. required
      // 2. all are optional
      // 3. some are required, some are optional
      // @see \Drupal\Core\Config\TypedConfigManager::getPossibleTypes
      $possible_types = $this->typedConfigManager->getPossibleTypes($original_type);
      $possible_type_definitions = array_intersect_key($all_type_definitions, array_fill_keys($possible_types, TRUE));

      // Determine the appropriate bucket. Each of the dynamically typed keys is
      // either:
      $required_possible_types = array_filter(
        $possible_type_definitions,
        fn (array $raw_def) => !array_key_exists('requiredKey', $raw_def)
      );
      switch (count($required_possible_types)) {
        // 1. Unconditionally required: if all possible types have `requiredKey`
        //    set.
        case count($possible_types):
          // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
          $bucket = &$dyn_typed_keys_unconditionally_required;
          break;

        // 2. Unconditionally optional: if 0 of all possible types have
        //    `requiredKey` set.
        case 0:
          // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
          $bucket = &$dyn_typed_keys_unconditionally_optional;
          break;

        // 3. Some are required, some are optional.
        default:
          // - Conditionally required if the resolved type has `requiredKey` set
          //   but not all possible types do.
          if (!array_key_exists('requiredKey', $resolved_element_definition->toArray())) {
            // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
            $bucket = &$dyn_typed_keys_conditionally_required;
          }
          // - Conditionally optional if the resolved type does not have
          //   `requiredKey` set and not all possible types do.
          else {
            // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
            $bucket = &$dyn_typed_keys_conditionally_optional;
          }
          break;
      }

      // Assign to the determined bucket.
      $bucket[$key] = $resolved_element_definition;
    }

    // Verify that every single one of the key-value pairs in the resolved
    // mapping is assigned to one of the 6 buckets.
    // phpcs:disable
    assert([] === array_diff_key($resolved_mapping_definition,
      // Static types (for example: `type: something.foo`).
      $unconditionally_required_keys + $unconditionally_optional_keys
      // Dynamic types (for example: `type: something.foo_[%parent.type]`).
      + $dyn_typed_keys_unconditionally_required + $dyn_typed_keys_conditionally_required
      + $dyn_typed_keys_unconditionally_optional + $dyn_typed_keys_conditionally_optional
    ));
    // phpcs:enable

    // Time to use the 6 buckets. Only 4 of them are used: the 2 unconditionally
    // optional ones (for static and dynamic types) must not trigger a
    // validation error, precisely because they're optional.
    return [
      // - The 2 unconditionally required buckets: self::$message.
      'unconditional' => array_keys($unconditionally_required_keys + $dyn_typed_keys_unconditionally_required),
      // - The 1 conditionally required buckets: self::$conditionalMessage.
      'conditional' => array_keys($dyn_typed_keys_conditionally_required),
      // - The 1 conditionally optional bucket: self::$extraneousMessage,
      //   because when the condition is not met (i.e. it is optional), it
      //   should not be present. Otherwise it is just noise.
      'extraneous' => array_keys($dyn_typed_keys_conditionally_optional),
    ];
  }

  /**
   * Resolves a `type: mapping` instance to its data definitions.
   *
   * @param \Drupal\Core\Config\Schema\Mapping $mapping
   *   A `type: mapping` instance, with values.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   The data definition for each of the keys.
   */
  protected function resolveMapping(Mapping $mapping) : array {
    $definition = $mapping->getDataDefinition();
    assert($definition instanceof MapDataDefinition);
    assert(self::validateMappingConfigSchemaDefinition($definition));

    // TRICKY: Mapping::getElementDefinition() because it is protected.
    // TRICKY: This also cannot use Mapping::getElements() because it only
    // considers keys that are present, making that impossible to use
    // detecting missing keys.
    // @todo Consider adding ArrayElement::getSchemaElements() that does not
    // look at the keys that are present, but the keys that are defined in the
    // schema. That would allow this to be removed.
    $resolved_mapping = [];
    // @see \Drupal\Core\Config\Schema\Mapping::getElementDefinition()
    $elements = $mapping->getElements();
    foreach ($definition['mapping'] as $key => $element_definition) {
      $resolved_mapping[$key] = $this->typedConfigManager->buildDataDefinition(
        $element_definition,
        array_key_exists($key, $elements) ? $elements[$key]->getValue() : NULL,
        $key,
        $mapping
      );
    }
    assert(self::isArrayOfDataDefinitions($resolved_mapping));
    return $resolved_mapping;
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
