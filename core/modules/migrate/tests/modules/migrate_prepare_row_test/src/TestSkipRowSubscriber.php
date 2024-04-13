<?php

namespace Drupal\migrate_prepare_row_test;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\MigrateSkipRowException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Skips a row in a test migration.
 */
class TestSkipRowSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [MigrateEvents::PREPARE_ROW => 'skipRow3'];
  }

  /**
   * Skips the row 3 from migrations tagged with 'prepare_row test'.
   *
   * @param \Drupal\migrate\Event\MigratePreRowSaveEvent $event
   *   The pre row save event.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   *   When row ID is 3.
   */
  public function skipRow3(MigratePreRowSaveEvent $event): void {
    if ($event->getRow()->getSourceProperty('id') === 3 &&
      in_array('prepare_row test', $event->getMigration()->getMigrationTags(), TRUE)) {
      throw new MigrateSkipRowException('skipped row 3', TRUE);
    }
  }

}
