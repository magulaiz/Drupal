<?php

namespace Drupal\dblog;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\dblog\Entity\DblogEntryInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for dblog entry entities.
 */
class DblogEntryStorage extends SqlContentEntityStorage implements DblogEntryStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function deleteAll(int $keep = 0): void {
    if ($keep < 0) {
      throw new \InvalidArgumentException('Value for $keep cannot be negative.');
    }

    if (empty($keep)) {
      $this->database->truncate('watchdog')->execute();
      return;
    }

    // For row limit n, get the wid of the nth row in descending wid order.
    // Counting the most recent n rows avoids issues with wid number sequences,
    // e.g. auto_increment value > 1 or rows deleted directly from the table.
    $connection = $this->database;
    $min_row = $connection->select('watchdog', 'w')
      ->fields('w', ['wid'])
      ->orderBy('wid', 'DESC')
      ->range($keep - 1, 1)
      ->execute()->fetchField();

    // Delete all table entries older than the nth row, if nth row was found.
    if ($min_row) {
      $connection->delete('watchdog')
        ->condition('wid', $min_row, '<')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function messageTypes(): array {
    return $this->database->query('SELECT DISTINCT([type]) FROM {watchdog} ORDER BY [type]')
      ->fetchAllKeyed(0, 0);
  }

  /**
   * {@inheritdoc}
   */
  public function save(EntityInterface $entity) {
    if ($entity->isNew()) {
      throw new EntityStorageException('Use dblog.logger service to create log entities.');
    }
    throw new EntityStorageException('Dblog entries are read only entities. Saving them once created is not allowed.');
  }

  /**
   * {@inheritdoc}
   */
  public function mostFrequentLogEntries(string $type): array {
    $query = $this->database->select('watchdog', 'w');
    $query->addExpression('COUNT([wid])', 'count');
    $query = $query
      ->fields('w', ['message', 'variables'])
      ->condition('w.type', $type)
      ->groupBy('message')
      ->groupBy('variables')
      ->range(0, 30)
      ->orderBy('count', 'desc');
    $items = $query->execute();

    if (empty($items)) {
      return [];
    }

    $entity_class = $this->getEntityClass();
    $top_logs = [];
    foreach ($items as $item) {
      $log = $entity_class::create([
        'message' => $item->message,
        'variables' => $item->variables,
      ]);

      $top_logs[] = [
        'count' => $item->count,
        'entry' => $log,
      ];
    }
    return $top_logs;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMostRecent(array $properties = []): ?DblogEntryInterface {
    // Build a query to fetch the entity IDs.
    $query = $this->getQuery();
    $query->accessCheck(FALSE);
    foreach ($properties as $name => $value) {
      // Cast scalars to array so we can consistently use an IN condition.
      $query->condition($name, (array) $value, 'IN');
    }
    $query->sort('wid', 'DESC');
    $query->range(0, 1);

    $ids = $query->execute();
    if (empty($ids)) {
      return NULL;
    }
    return $this->load(reset($ids));
  }

}
