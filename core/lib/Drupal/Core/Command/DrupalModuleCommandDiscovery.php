<?php

namespace Drupal\Core\Command;

use Drupal\Core\DrupalKernel;
use Symfony\Component\Console\Application;

/**
 * Provides a command to import a database generation script.
 */
class DrupalModuleCommandDiscovery {

  public static function addCommands(Application $app, DrupalKernel $kernel) {
    $app->setCommandLoader($kernel->getContainer()->get('console.command_loader'));
    // Add commands which did not use an attribute, relying solely on configure().
    if ($kernel->getContainer()->hasParameter('console.command.ids')) {
      foreach ($kernel->getContainer()->getParameter('console.command.ids') as $id) {
        $app->add($kernel->getContainer()->get($id));
      }
    }
  }

}
