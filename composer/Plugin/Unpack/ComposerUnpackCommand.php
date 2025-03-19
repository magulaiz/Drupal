<?php

namespace Drupal\Composer\Plugin\Unpack;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "drupal:unpack" command class.
 *
 * Manually run the unpack operation that normally happens after
 * 'composer install'.
 *
 * @internal
 */
class ComposerUnpackCommand extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('drupal:unpack')
      ->setAliases(['unpack'])
      ->setDescription('Unpack Drupal recipes.')
      ->addArgument('package', InputArgument::REQUIRED)
      ->setHelp(
        <<<EOT
The <info>drupal:unpack</info> command unpacks a package's dependencies into the
composer.json file.

<info>php composer.phar drupal:unpack package-name</info>

It is usually not necessary to call <info>drupal:unpack</info> manually,
because by default it is called automatically as needed, e.g. after an
<info>install</info> or <info>update</info> command.
EOT
            );

  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $composer = $this->requireComposer();
    $package_name = $input->getArgument('package');
    $packages = $composer->getRepositoryManager()->getLocalRepository()->findPackages($package_name);
    if (empty($packages)) {
      $output->writeln("<error>No packages matching $package_name found.</error>");
      return 1;
    }
    $package = reset($packages);
    $manager = new UnpackManager($this->requireComposer(), $this->getIO());
    $manager->registerPackage($package);
    $manager->unpack();
    return 0;
  }

}
