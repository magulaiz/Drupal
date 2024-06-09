<?php

declare(strict_types=1);

namespace Drupal\dex_test\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A private command.
 *
 * This command must not have an attribute or name via configure().
 */
final class DexExamplePrivateCommand extends Command {

  /**
   * {@inheritdoc}
   */
  public function getName(): ?string {
    return 'example:command-private';
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $io->success('Done with private command.');

    return static::SUCCESS;
  }

}
