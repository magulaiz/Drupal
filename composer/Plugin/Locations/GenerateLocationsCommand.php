<?php

namespace Drupal\Composer\Plugin\Locations;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "drupal:locations" command class.
 *
 * Manually run the writing of the DrupalLocations class.
 *
 * @internal
 */
class GenerateLocationsCommand extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('drupal:locations')
      ->setAliases(['locations'])
      ->setDescription('Write the DrupalLocations class.')
      ->setHelp(
        <<<EOT
        The <info>drupal:locations</info> command writes a class which records
        the location of Drupal's files within the Composer project.

        <info>php composer.phar drupal:locations</info>

        It is usually not necessary to call <info>drupal:locations</info>
        manually, because it is called automatically as needed, e.g. after an
        <info>install</info> or <info>update</info> command.
        EOT
        );

  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    GenerateLocationsClass::generate($this->getComposer(), $this->getIO());

    return 0;
  }

}
