<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @internal
 *   This API is experimental.
 */
abstract class InputCollectorBase {

  public function __construct(
    private readonly TypedDataManagerInterface $typedDataManager,
  ) {}

  /**
   * Collects input values for a recipe and the recipes it depends on.
   *
   * Once recipes' input values have been collected, those values cannot be
   * changed.
   *
   * @param \Drupal\Core\Recipe\Recipe $recipe
   *   The recipe to collect input values for.
   */
  public function collectAll(Recipe $recipe): void {
    static $processed = [];

    // Don't bother collecting values for a recipe we've already seen.
    if (in_array($recipe->path, $processed, TRUE)) {
      return;
    }

    // First, collect values for the recipe's dependencies.
    foreach ($recipe->recipes->recipes as $dependency) {
      $this->collectAll($dependency);
    }

    $values = [];
    foreach ($recipe->inputDefinitions as $key => $definition) {
      $value = $this->collectValue($recipe->machineName() . '.' . $key, $definition);

      /** @var array{constraints?: array<mixed>} $definition */
      if (isset($definition['constraints'])) {
        $this->validate($definition['constraints'], $value);
      }
      $values[$key] = $value;
    }
    $recipe->setInputValues($values);
    $processed[] = $recipe->path;
  }

  /**
   * Validates an input value against a set of constraints.
   *
   * @param array<mixed> $constraints
   *   The constraints to validate the value against, keyed by constraint name.
   *   The values are configuration arrays for the constraints.
   * @param mixed $value
   *   The value to validate.
   *
   * @throws \Symfony\Component\Validator\Exception\ValidationFailedException
   *   Thrown if the given value did not pass all of the constraints.
   */
  protected function validate(array $constraints, mixed $value): void {
    $data_definition = DataDefinition::create('any')
      ->setConstraints($constraints);

    $violations = $this->typedDataManager->create($data_definition, $value)
      ->validate();
    if (count($violations) > 0) {
      throw new ValidationFailedException($value, $violations);
    }
  }

  /**
   * Collects a single input value.
   *
   * @param string $name
   *   The name of the input definition, prefixed with the recipe's machine name
   *   in the form `RECIPE_NAME.INPUT_NAME`.
   * @param array<mixed> $definition
   *   The input definition.
   *
   * @return mixed
   *   The collected value.
   */
  abstract protected function collectValue(string $name, array $definition): mixed;

}
