<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Event;

/**
 * Represents the beginning of a transaction.
 */
class TransactionBeginEvent extends DatabaseEvent {

  /**
   * Constructs a TransactionBeginEvent object.
   *
   * See 'Customizing database settings' in settings.php for an explanation of
   * the $key and $target connection values.
   *
   * @param string $key
   *   The database connection key.
   * @param string $target
   *   The database connection target.
   * @param string $name
   *   The name of the transaction.
   */
  public function __construct(
    public readonly string $key,
    public readonly string $target,
    public readonly string $name,
  ) {
    parent::__construct();
  }

}
