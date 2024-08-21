<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\Utility\CallableResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Collects input values for recipes from the command line.
 *
 * @internal
 *   This API is experimental.
 */
final class ConsoleInputCollector extends InputCollectorBase implements ContainerInjectionInterface {

  /**
   * The name of the command-line option for passing input values.
   *
   * @var string
   */
  public const INPUT_OPTION = 'input';

  public function __construct(
    private readonly DefaultValueResolver $defaultValueResolver,
    private readonly CallableResolver $callableResolver,
    private readonly ?InputInterface $input,
    private readonly ?StyleInterface $io,
    TypedDataManagerInterface $typedDataManager,
  ) {
    parent::__construct($typedDataManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ?InputInterface $input = NULL, ?StyleInterface $io = NULL): static {
    return new static(
      DefaultValueResolver::create($container),
      $container->get(CallableResolver::class),
      $input,
      $io,
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
    $command->addOption(static::INPUT_OPTION, 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'An input value to pass to the recipe or one of its dependencies, in the form `--input=RECIPE_NAME.INPUT_NAME=VALUE`.');
  }

  /**
   * {@inheritdoc}
   */
  protected function collectValue(string $name, array $definition): mixed {
    // If the value was passed as a `--input` option, return that.
    if ($this->input?->hasOption(static::INPUT_OPTION)) {
      foreach ($this->input->getOption(static::INPUT_OPTION) as $value) {
        if (str_starts_with($value, "$name=")) {
          return explode('=', $value, 2)[1];
        }
      }
    }

    /** @var array{prompt?: array{method: string, arguments?: array<mixed>}} $definition */
    $default_value = $this->defaultValueResolver->collectValue($name, $definition);
    // If there's no way to prompt the user (i.e., the I/O handler is
    // unavailable), or there's no information on how to prompt the user,
    // there's nothing else for us to do; return the default value.
    if (empty($this->io) || empty($definition['prompt'])) {
      return $default_value;
    }

    $method = $definition['prompt']['method'];
    $arguments = $definition['prompt']['arguments'] ?? [];

    foreach ($arguments as $name => $argument) {
      $type = (new \ReflectionParameter([StyleInterface::class, $method], $name))
        ->getType();
      // If the parameter must be a callable, run the argument through the
      // callable resolver.
      if ($argument && $type instanceof \ReflectionNamedType && $type->getName() === 'callable') {
        $arguments[$name] = $this->callableResolver->getCallableFromDefinition($argument);
      }
    }
    // Most of the input-collecting methods of StyleInterface have a `default`
    // parameter.
    $arguments += [
      'default' => $default_value,
    ];
    return $this->io->$method(...$arguments);
  }

}
