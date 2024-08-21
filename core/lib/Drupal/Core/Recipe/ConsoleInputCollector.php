<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\Utility\CallableResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Collects input values for recipes from the command line.
 *
 * @internal
 *   This API is experimental.
 */
final class ConsoleInputCollector extends InputCollectorBase implements ContainerInjectionInterface {

  public const INPUT_OPTION = 'input';

  public function __construct(
    private readonly DefaultValueResolver $defaultValueResolver,
    private readonly CallableResolver $callableResolver,
    private readonly InputInterface $input,
    private readonly StyleInterface $io,
    TypedDataManagerInterface $typedDataManager,
  ) {
    parent::__construct($typedDataManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ?InputInterface $input = NULL, ?StyleInterface $io = NULL): static {
    $input ??= new ArrayInput([]);

    return new static(
      DefaultValueResolver::create($container),
      $container->get(CallableResolver::class),
      $input,
      $io ?? new SymfonyStyle($input, new NullOutput()),
      $container->get(TypedDataManagerInterface::class),
    );
  }

  /**
   * Configures a console command to support the `--input` option.
   *
   * This should be called by a command's configure() method.
   *
   * @param \Symfony\Component\Console\Command\Command $command
   *   The command being configured.
   */
  public static function configureCommand(Command $command): void {
    $command->addOption(self::INPUT_OPTION, 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'An input value to pass to the recipe or one of its dependencies, in the form `--input=RECIPE_NAME.INPUT_NAME=VALUE`.');
  }

  /**
   * Returns the description for all inputs of a recipe and its dependencies.
   *
   * @param \Drupal\Core\Recipe\Recipe $recipe
   *   The recipe to examine.
   *
   * @return string[]
   *   The descriptions of every input defined by the recipe and its
   *   dependencies, keyed by the input's fully qualified name (i.e., prefixed
   *   by the name of the recipe that defines it).
   */
  private function getDescriptions(Recipe $recipe): array {
    $descriptions = [];
    foreach ($recipe->recipes->recipes as $dependency) {
      $descriptions = array_merge($descriptions, $this->getDescriptions($dependency));
    }
    foreach ($recipe->inputDefinitions as $key => $definition) {
      $name = $recipe->machineName() . '.' . $key;
      $descriptions[$name] = $definition['description'];
    }
    return $descriptions;
  }

  /**
   * Lists every input defined by a recipe and its dependencies.
   *
   * @param \Drupal\Core\Recipe\Recipe $recipe
   *   The recipe being examined.
   */
  public function listAll(Recipe $recipe): void {
    $descriptions = $this->getDescriptions($recipe);

    // Passing NULL as the callable to array_map() makes it act like a Python
    // zip() operation.
    // @see https://docs.python.org/3.8/library/functions.html#zip
    $rows = array_map(NULL, array_keys($descriptions), $descriptions);
    $this->io->table(['Name', 'Description'], $rows);
  }

  /**
   * {@inheritdoc}
   */
  protected function collectValue(string $name, array $definition): mixed {
    /** @var array{prompt?: array{method: string, arguments?: array<mixed>, data_type?: string}} $definition */
    $cast = function ($value) use ($definition): mixed {
      $data_type = $definition['prompt']['data_type'] ?? NULL;

      return match ($data_type) {
        'string' => strval($value),
        'integer' => intval($value),
        'float' => floatval($value),
        'boolean' => boolval($value),
        default => $value,
      };
    };

    // If the value was passed as a `--input` option, return that.
    if ($this->input->hasOption('input')) {
      /** @var string[] $input_options */
      $input_options = $this->input->getOption('input');

      foreach ($input_options as $value) {
        if (str_starts_with($value, "$name=")) {
          return $cast(explode('=', $value, 2)[1]);
        }
      }
    }

    $default_value = $this->defaultValueResolver->collectValue($name, $definition);
    // If there's no information on how to prompt the user, there's nothing else
    // for us to do; return the default value.
    if (empty($definition['prompt'])) {
      return $default_value;
    }

    $arguments = $definition['prompt']['arguments'] ?? [];
    // Most of the input-collecting methods of StyleInterface have a `default`
    // parameter.
    $arguments += [
      'default' => $default_value,
    ];
    return $cast($this->prompt($definition['prompt']['method'], $arguments));
  }

  /**
   * Collects an input value by interacting with the user.
   *
   * @param string $method
   *   The name of a method of \Symfony\Component\Console\Style\StyleInterface
   *   that returns a value.
   * @param array<mixed> $arguments
   *   An array of arguments, keyed by parameter name, to pass to the prompt
   *   method.
   *
   * @return mixed
   *   The collected value.
   */
  private function prompt(string $method, array $arguments): mixed {
    foreach ($arguments as $name => $argument) {
      $parameter = new \ReflectionParameter([StyleInterface::class, $method], $name);

      // If the parameter must be a callable, run the argument through the
      // callable resolver.
      $type = $parameter->getType();
      if ($argument && $type instanceof \ReflectionNamedType && $type->getName() === 'callable') {
        /** @var array<mixed>|(callable(): mixed)|string $argument */
        $arguments[$name] = $this->callableResolver->getCallableFromDefinition($argument);
      }
    }
    return $this->io->$method(...$arguments);
  }

}
