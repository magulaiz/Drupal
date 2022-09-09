<?php

namespace Drupal\dblog;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\dblog\Entity\DblogEntryInterface;

/**
 * Defines an interface for dblog entry entity storage classes.
 */
interface DblogEntryStorageInterface extends ContentEntityStorageInterface {

  /**
   * Deletes all the dblog entries.
   *
   * @param int $keep
   *   (optional). When specified, $keep most recent log entries will be kept.
   *
   * @throws \InvalidArgumentException
   */
  public function deleteAll(int $keep = 0) : void;

  /**
   * Gathers a list of uniquely defined database log message types.
   *
   * @return string[]
   *   List of uniquely defined database log message types.
   */
  public function messageTypes() : array;

  /**
   * Returns a list of most frequent log entries of a given type.
   *
   * @param string $type
   *   The type of message to filter by.
   *
   * @return array
   *   An array of more frequent event occurrences.  Each occurrence is a pair
   *   of 'count' and 'entry' where count is number of times the event was
   *   logged and 'entry' an instance of
   *   Drupal\dblog\Entity\DblogEntryInterface. Occurrences should be sorted by
   *   count in descending order.
   */
  public function mostFrequentLogEntries(string $type) : array;

  /**
   * Loads the most recent log entry.
   *
   * This methods behaves like loadByProperties, but returns the most recent
   * item.
   *
   * @param array $properties
   *   An associative array where the keys are the property names and the
   *   values are the values those properties must have.
   *
   * @return \Drupal\dblog\Entity\DblogEntryInterface|null
   *   The most recent log event that matches with the specified properties.
   */
  public function loadMostRecent(array $properties = []) : ?DblogEntryInterface;

}
