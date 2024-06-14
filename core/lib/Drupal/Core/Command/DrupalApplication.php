<?php

namespace Drupal\Core\Command;

use Drupal\Core\Recipe\RecipeCommand;
use Symfony\Component\Console\Application;

/**
 * Provides a command to import a database generation script.
 */
class DrupalApplication extends Application {

  /**
   * {@inheritdoc}
   */
  public function __construct(protected object $classloader) {
    parent::__construct('drupal', \Drupal::VERSION);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultCommands(): array {
    $default_commands = parent::getDefaultCommands();

    $default_commands[] = new QuickStartCommand();
    $default_commands[] = new InstallCommand($this->classloader);
    $default_commands[] = new ServerCommand($this->classloader);
    $default_commands[] = new GenerateTheme();
    $default_commands[] = new RecipeCommand($this->classloader);

    return $default_commands;
  }

}
