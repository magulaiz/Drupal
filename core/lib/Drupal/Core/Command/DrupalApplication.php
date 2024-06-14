<?php

namespace Drupal\Core\Command;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Recipe\RecipeCommand;
use Drupal\Core\Site\Settings;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a command to import a database generation script.
 */
class DrupalApplication extends Application {

  /**
   * {@inheritdoc}
   */
  public function __construct(protected object $classloader, protected array $context) {
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

  /**
   * @inheritdoc
   */
  public function find($name): Command {
    try {
      $command = parent::find($name);
      if ($command instanceof ListCommand || $command instanceof HelpCommand) {
        $this->bootstrap();
      }
      return $command;
    } catch (CommandNotFoundException $e) {
      if (!$this->bootstrap()) {
        throw $e;
      }
      return parent::find($name);
    }
  }

  protected function bootstrap(): bool {
    try {
      // Discovery can get out of whack if cleared caches and try to run this
      // command without a web request priming discovery.
      chdir(\DRUPAL_ROOT);
      $kernel = new DrupalKernel('prod', $this->classloader, FALSE);
      $kernel::bootEnvironment();
      $kernel->setSitePath($context['DRUPAL_DEV_SITE_PATH'] ?? 'sites/default');
      Settings::initialize($kernel->getAppRoot(), $kernel->getSitePath(), $this->classloader);
      $kernel->boot();

      // Drupal is highly dependent on a Request:
      $request = Request::createFromGlobals();
      $baseDomain = $context['HOST'] ?? NULL;
      $basePort = $context['PORT'] ?? NULL;
      if ($baseDomain !== NULL) {
        $request->server->set('SERVER_NAME', $baseDomain);
        $request->server->set('SERVER_PORT', $basePort ?? 80);
      }
      $kernel->getContainer()
        ->get('request_stack')
        ->push($request);
      // This sets things up, esp loadLegacyIncludes().
      $kernel->preHandle($request);

      DrupalModuleCommandDiscovery::addCommands($this, $kernel);
    } catch(\Exception $e) {
      return false;
    }
    return true;
  }
}
