<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Collects and validates input values for a recipe.
 *
 * @internal
 *   This API is experimental.
 */
final class InputConfigurator {

  /**
   * The collected input values, or NULL if none have been collected yet.
   *
   * @var mixed[]|null
   */
  private ?array $values = NULL;

  /**
   * @param array<string, array<string, mixed>> $definitions
   *   The recipe's input definitions, keyed by name. This is an array of arrays
   *   where each sub-array has, at minimum:
   *   - `description`: A short, human-readable description of the input (e.g.,
   *      what the recipe uses it for).
   *   - `data_type`: An optional Typed Data data type for the input value.
   *      Defaults to `any`.
   *   - `constraints`: An optional array of validation constraints to apply
   *     to the value. This should be an associative array of arrays, keyed by
   *     constraint name, where each sub-array is a set of options for that
   *     constraint (identical to the way validation constraints are defined in
   *     config schema).
   *   - `default`: A default value for the input, if it cannot be collected
   *     the user. See ::getDefaultValue() for more information.
   * @param \Drupal\Core\Recipe\RecipeConfigurator $dependencies
   *   The recipes that this recipe depends on.
   * @param string $prefix
   *   A prefix for each input definition, to give each one a unique name
   *   when collecting input for multiple recipes. Usually this is the unique
   *   name of the recipe.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    private readonly array $definitions,
    private readonly RecipeConfigurator $dependencies,
    private readonly string $prefix,
    private readonly TypedDataManagerInterface $typedDataManager,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Returns the collected input values, keyed by name.
   *
   * @return mixed[]
   *   The collected input values, keyed by name.
   */
  public function getValues(): array {
    return $this->values ?? [];
  }

  /**
   * Returns the description for all inputs of this recipe and its dependencies.
   *
   * @return string[]
   *   The descriptions of every input defined by the recipe and its
   *   dependencies, keyed by the input's fully qualified name (i.e., prefixed
   *   by the name of the recipe that defines it).
   */
  public function describeAll(): array {
    $descriptions = [];
    foreach ($this->dependencies->recipes as $dependency) {
      $descriptions = array_merge($descriptions, $dependency->input->describeAll());
    }
    foreach ($this->definitions as $key => $definition) {
      $name = $this->prefix . '.' . $key;
      $descriptions[$name] = $definition['description'];
    }
    return $descriptions;
  }

  /**
   * Collects input values for this recipe and its dependencies.
   *
   * @param \Drupal\Core\Recipe\InputCollectorInterface $collector
   *   The input collector to use.
   *
   * @throws \Symfony\Component\Validator\Exception\ValidationFailedException
   *   Thrown if any of the collected values violate their validation
   *   constraints.
   */
  public function collectAll(InputCollectorInterface $collector): void {
    if (is_array($this->values)) {
      throw new \LogicException('Input values cannot be changed once they have been set.');
    }

    // Don't bother collecting values for a recipe we've already seen.
    static $processed = [];
    if (in_array($this->prefix, $processed, TRUE)) {
      return;
    }

    // First, collect values for the recipe's dependencies.
    /** @var \Drupal\Core\Recipe\Recipe $dependency */
    foreach ($this->dependencies->recipes as $dependency) {
      $dependency->input->collectAll($collector);
    }

    $this->values = [];
    foreach ($this->definitions as $key => $definition) {
      $value = $collector->collectValue(
        $this->prefix . '.' . $key,
        $definition['description'],
        $definition,
        $this->getDefaultValue($definition['default']),
      );

      // Use typed data to validate and cast the value, if needed.
      $data_definition = $this->typedDataManager->createDataDefinition($definition['data_type'] ?? 'any')
        ->setConstraints($definition['constraints'] ?? []);
      $data = $this->typedDataManager->create($data_definition, $value);
      $violations = $data->validate();
      if (count($violations) > 0) {
        throw new ValidationFailedException($value, $violations);
      }
      if ($data instanceof PrimitiveInterface) {
        $value = $data->getCastedValue();
      }
      $this->values[$key] = $value;
    }
    $processed[] = $this->prefix;
  }

  /**
   * Returns the default value for an input definition.
   *
   * @param array $definition
   *   An input definition. Must contain a `source` element, which can be either
   *   'config' or 'value'. If `source` is 'config', then there must also be a
   *   `config` element, which is a two-element indexed array containing
   *   (in order) the name of an extant config object, and a property path
   *   within that object. If `source` is 'value', then there must be a `value`
   *   element, which will be returned as-is.
   *
   * @return mixed
   *   The default value.
   */
  private function getDefaultValue(array $definition): mixed {
    if ($definition['source'] === 'config') {
      [$name, $key] = $definition['config'];
      $config = $this->configFactory->get($name);
      if ($config->isNew()) {
        throw new \RuntimeException("The '$name' config object does not exist.");
      }
      return $config->get($key);
    }
    return $definition['value'];
  }

}
