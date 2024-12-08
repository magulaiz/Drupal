<?php

namespace Drupal\Component\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Provides a factory returning the event dispatcher service.
 */
abstract class EventDispatcherFactory {

  private static EventDispatcher $eventDispatcher;

  public static function getInstance(): EventDispatcher {
    if (!isset(self::$eventDispatcher)) {
      self::$eventDispatcher = new EventDispatcher();
    }
    return self::$eventDispatcher;
  }

}
