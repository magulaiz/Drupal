<?php

declare(strict_types=1);

namespace Drupal\system\Command;

use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Example module command.
 *
 * Ultimately, I think we should remove this from the MR, but it is
 * useful to have an example command to test with during development.
 */
#[AsCommand(name: 'system:example', description: 'An example command.')]
final class Example extends Command {

  public function __construct(
    private readonly TimeInterface $dateTime,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $now = new \DateTimeImmutable('@' . $this->dateTime->getRequestTime());
    $io->note('The current time is ' . $now->format('r'));

    return static::SUCCESS;
  }

}
