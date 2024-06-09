<?php

declare(strict_types=1);

namespace Drupal\dex_test\Command;

use Drupal\autowire_test\TestService;
use Drupal\Core\Url;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * An example command.
 */
#[AsCommand(name: 'example:command', description: 'An example command.')]
final class DexExampleCommand extends Command {

  /**
   * Constructs a command with autowiring.
   */
  public function __construct(
    private readonly TestService $testService,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->addArgument('argument-test', mode: InputArgument::OPTIONAL)
      ->addArgument('scenario', mode: InputArgument::OPTIONAL)
      ->addOption('option-test', mode: InputOption::VALUE_NONE);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $scenario = $input->getArgument('scenario');
    if ('absolute_url' === $scenario) {
      $io->note('Base Url test: ' . Url::fromUserInput('/abc')->setAbsolute()->toString());

      return static::SUCCESS;
    }

    $io->note('Option test: ' . ($input->getOption('option-test') ? 'Yes' : 'No'));
    $io->note('Argument test: ' . ($input->getArgument('argument-test') ? 'Yes' : 'No'));
    $io->note('Dependency injection test: ' . $this->testService->getTestInjection()::class);
    $io->success('Done.');

    return static::SUCCESS;
  }

}
