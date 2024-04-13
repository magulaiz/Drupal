<?php

namespace Drupal\migrate\Plugin;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Event\ImportAwareInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\Event\MigrateRollbackEvent;
use Drupal\migrate\Event\RollbackAwareInterface;
use Drupal\migrate\MigrateSkipRowException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to forward Migrate events to source and destination plugins.
 */
class PluginEventSubscriber implements EventSubscriberInterface {

  /**
   * PluginEventSubscriber constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler
  ) {}

  /**
   * Tries to invoke event handling methods on source and destination plugins.
   *
   * @param string $method
   *   The method to invoke.
   * @param \Drupal\migrate\Event\MigrateImportEvent|\Drupal\migrate\Event\MigrateRollbackEvent $event
   *   The event that has triggered the invocation.
   * @param string $plugin_interface
   *   The interface which plugins must implement in order to be invoked.
   */
  protected function invoke($method, $event, $plugin_interface) {
    $migration = $event->getMigration();

    $source = $migration->getSourcePlugin();
    if ($source instanceof $plugin_interface) {
      call_user_func([$source, $method], $event);
    }

    $destination = $migration->getDestinationPlugin();
    if ($destination instanceof $plugin_interface) {
      call_user_func([$destination, $method], $event);
    }
  }

  /**
   * Forwards pre-import events to the source and destination plugins.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function preImport(MigrateImportEvent $event) {
    $this->invoke('preImport', $event, ImportAwareInterface::class);
  }

  /**
   * Forwards post-import events to the source and destination plugins.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event.
   */
  public function postImport(MigrateImportEvent $event) {
    $this->invoke('postImport', $event, ImportAwareInterface::class);
  }

  /**
   * Forwards pre-rollback events to the source and destination plugins.
   *
   * @param \Drupal\migrate\Event\MigrateRollbackEvent $event
   *   The rollback event.
   */
  public function preRollback(MigrateRollbackEvent $event) {
    $this->invoke('preRollback', $event, RollbackAwareInterface::class);
  }

  /**
   * Forwards post-rollback events to the source and destination plugins.
   *
   * @param \Drupal\migrate\Event\MigrateRollbackEvent $event
   *   The rollback event.
   */
  public function postRollback(MigrateRollbackEvent $event) {
    $this->invoke('postRollback', $event, RollbackAwareInterface::class);
  }

  /**
   * Runs deprecated prepare row hook implementations.
   *
   * @param \Drupal\migrate\Event\MigratePreRowSaveEvent $event
   *   Prepare row event object.
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0.
   *   Replace hook implementations with
   *   \Drupal\migrate\Event\MigrateEvents::PREPARE_ROW event subscribers. In
   *   order to skip the row, throw \Drupal\migrate\MigrateSkipRowException in
   *   the event subscriber.
   *
   * @see https://www.drupal.org/node/2952459
   */
  public function hookPrepareRow(MigratePreRowSaveEvent $event) {
    $deprecation_message = 'Replace hook implementations with \Drupal\migrate\Event\MigrateEvents::PREPARE_ROW event subscribers. In order to skip the row, throw \Drupal\migrate\MigrateSkipRowException in the event subscriber. See https://www.drupal.org/node/2952459';
    $result_hook = $this->moduleHandler->invokeAllDeprecated($deprecation_message, 'migrate_prepare_row', [
      $event->getRow(),
      $event->getMigration()->getSourcePlugin(),
      $event->getMigration(),
    ]);
    // We will skip if migrate_prepare_row hook returned FALSE.
    if ($result_hook && in_array(FALSE, $result_hook, TRUE)) {
      throw new MigrateSkipRowException('The hook migrate_prepare_row has skipped this row.');
    }
    $hook = 'migrate_' . $event->getMigration()->id() . '_prepare_row';
    $result_named_hook = $this->moduleHandler->invokeAllDeprecated($deprecation_message, $hook, [
      $event->getRow(),
      $event->getMigration()->getSourcePlugin(),
      $event->getMigration(),
    ]);
    // We will skip if migrate_MIGRATION_ID_prepare_row hook returned FALSE.
    if ($result_named_hook && in_array(FALSE, $result_named_hook, TRUE)) {
      throw new MigrateSkipRowException(sprintf('The hook %s has skipped this row.', $hook));
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[MigrateEvents::PRE_IMPORT][] = ['preImport'];
    $events[MigrateEvents::POST_IMPORT][] = ['postImport'];
    $events[MigrateEvents::PRE_ROLLBACK][] = ['preRollback'];
    $events[MigrateEvents::POST_ROLLBACK][] = ['postRollback'];
    $events[MigrateEvents::PREPARE_ROW][] = ['hookPrepareRow'];

    return $events;
  }

}
