<?php

namespace Drupal\Composer\Plugin\Unpack;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin for handling dependency unpacking.
 *
 * @internal
 */
final class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * The handler for dependency unpacking.
   */
  private UnpackManager $manager;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io): void {
    $this->manager = new UnpackManager($composer, $io);
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io): void {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io): void {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PackageEvents::POST_PACKAGE_INSTALL => 'postPackage',
      ScriptEvents::POST_AUTOLOAD_DUMP => 'postCmd',
    ];
  }

  /**
   * Post package event behavior.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Composer package event sent on install/update/remove.
   */
  public function postPackage(PackageEvent $event): void {
    $this->manager->registerPackage($this->getPackage($event));
  }

  /**
   * Post autoload event callback.
   */
  public function postCmd(): void {
    $this->manager->unpack();
  }

  /**
   * Get the package from a package event.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Composer package event sent on install/update/remove.
   *
   * @return \Composer\Package\PackageInterface
   *   The package from the event.
   */
  private static function getPackage(PackageEvent $event): PackageInterface {
    $operation = $event->getOperation();
    return match (get_class($operation)) {
      InstallOperation::class => $operation->getPackage(),
      UpdateOperation::class => $operation->getTargetPackage(),
    };
  }

}
