<?php

namespace Drupal\Core\Database\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Represents a database event.
 */
abstract class DatabaseEvent extends Event {

  /**
   * The time of the event.
   */
  public readonly float $time;

  /**
   * Constructs a DatabaseEvent object.
   */
  public function __construct() {
    $this->time = microtime(TRUE);
  }

}
