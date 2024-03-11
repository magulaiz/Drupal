<?php

namespace Drupal\migrate\Event;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Row;

/**
 * Wraps a skip event for event listeners.
 */
class MigrateRowSkippedEvent extends EventBase {

  /**
   * Row object.
   *
   * @var \Drupal\migrate\Row
   */
  protected $row;

  /**
   * Constructs a skip event object.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   Migration entity.
   * @param \Drupal\migrate\MigrateMessageInterface $message
   *   The current migrate message service.
   * @param \Drupal\migrate\Row $row
   *   The row object.
   * @param \Throwable $exception
   *   The exception that was thrown.
   */
  public function __construct(MigrationInterface $migration, MigrateMessageInterface $message, Row $row, protected \Throwable $exception) {
    parent::__construct($migration, $message);
    $this->row = $row;
  }

  /**
   * Gets the row object.
   *
   * @return \Drupal\migrate\Row
   *   The skipped row object.
   */
  public function getRow() {
    return $this->row;
  }

  /**
   * Get the exception that was thrown.
   */
  public function getException(): \Throwable {
    return $this->exception;
  }

}
