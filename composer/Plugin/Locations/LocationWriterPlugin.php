<?php

namespace Drupal\Composer\Plugin\Locations;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin for writing the DrupalLocations class.
 *
 * @internal
 */
class LocationWriterPlugin implements PluginInterface, Capable, EventSubscriberInterface {

  /**
   * The Composer service.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * Composer's I/O service.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return [CommandProvider::class => LocationsCommandProvider::class];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ScriptEvents::POST_UPDATE_CMD => 'writeLocationsClass',
      ScriptEvents::POST_INSTALL_CMD => 'writeLocationsClass',
    ];
  }

  /**
   * POST_UPDATE_CMD and POST_INSTALL_CMD event handler.
   *
   * @param \Composer\Script\Event $event
   *   The Composer event.
   */
  public function writeLocationsClass(Event $event) {
    GenerateLocationsClass::generate($this->composer, $this->io);
  }

}
