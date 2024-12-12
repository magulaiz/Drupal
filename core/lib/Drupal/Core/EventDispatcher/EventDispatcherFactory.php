<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a factory returning the event dispatcher service.
 */
class EventDispatcherFactory implements EventDispatcherFactoryInterface {

  private static EventDispatcherInterface $eventDispatcher;
  private static EventDispatcherFactoryStage $stage;

  public static function createInstance(EventDispatcherFactoryStage|string $stage = EventDispatcherFactoryStage::PreBootstrap): EventDispatcherInterface {
    self::$stage = is_string($stage) ? EventDispatcherFactoryStage::from($stage) : $stage;
    self::$eventDispatcher = new EventDispatcher();
    return self::$eventDispatcher;
  }

  public function getInstance(): EventDispatcherInterface {
    if (!isset(self::$eventDispatcher)) {
      return self::createInstance();
    }
    return self::$eventDispatcher;
  }

  public function getInstanceStage(): EventDispatcherFactoryStage {
    if (isset(self::$stage)) {
      return self::$stage;
    }
    throw new \LogicException('The event dispatcher has not been instantiated yet');
  }

}
