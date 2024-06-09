<?php

declare(strict_types=1);

namespace Drupal\dex_test\Command;

use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
    private readonly TimeInterface $dateTime,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $now = new \DateTimeImmutable('@' . $this->dateTime->getRequestTime());
    $io->note('The current time is ' . $now->format('r'));

    return static::SUCCESS;
  }

}
