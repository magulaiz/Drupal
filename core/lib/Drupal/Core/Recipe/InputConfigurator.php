<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class InputConfigurator {

  /**
   * The collected input values, or NULL if none have been collected yet.
   *
   * @var mixed[]|null
   */
  private ?array $values = NULL;

  /**
   * @param array<string, array<string, mixed>> $input_definitions
   *   The recipe's input definitions, keyed by name. This is an array of arrays
   *   where each sub-array has a `from` element, and the other elements vary
   *   depending on what `from` is.
   * @param \Drupal\Core\Recipe\RecipeConfigurator $dependencies
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
   * Collects input values for this recipe and its dependencies.
   *
   * @param \Drupal\Core\Recipe\InputCollectorInterface $collector
   *   The input collector to use.
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
    foreach ($recipe->recipes->recipes as $dependency) {
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

      $data_definition = $this->typedDataManager->createDataDefinition($definition['data_type'] ?? 'any');
      if (isset($definition['constraints'])) {
        $data_definition->setConstraints($definition['constraints']);
      }
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
