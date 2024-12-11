<?php

namespace Drupal\Component\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a factory returning the event dispatcher service.
 */
class EventDispatcherFactory {

  private static EventDispatcherInterface $eventDispatcher;

  public static function createInstance(string $context = 'singleton', bool $reset = FALSE): EventDispatcherInterface {
    if (!isset(self::$eventDispatcher) || $reset) {
      self::$eventDispatcher = new EventDispatcher();
    }
    return self::$eventDispatcher;
  }

  public function getInstance(): EventDispatcherInterface {
    if (!isset(self::$eventDispatcher)) {
      return self::createInstance();
    }
    return self::$eventDispatcher;
  }

}
