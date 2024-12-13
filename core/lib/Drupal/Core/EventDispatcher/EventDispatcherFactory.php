<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

use Drupal\Core\Database\EventSubscriber\StatementExecutionSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a factory returning the event dispatcher service.
 */
class EventDispatcherFactory implements EventDispatcherFactoryInterface {

  private static EventDispatcherInterface $eventDispatcher;
  private static EventDispatcherFactoryStage $stage;

  /**
   * Subscribers to be registered for early event dispatcher.
   *
   * Note that the early event dispatcher will be purged during container
   * bootstrap, so any subscriber/listener needed in later stages have to be
   * added also in the service container definition.
   */
  private readonly static array $preBootstrapSubscribers = [
    new StatementExecutionSubscriber();
  ];

  public static function createInstance(EventDispatcherFactoryStage|string $stage = EventDispatcherFactoryStage::PreBootstrap): EventDispatcherInterface {
    self::$stage = is_string($stage) ? EventDispatcherFactoryStage::from($stage) : $stage;
    self::$eventDispatcher = new EventDispatcher();
    if (self::$stage !== EventDispatcherFactoryStage::FullContainer) {
      foreach (self::$preBootstrapSubscribers as $subscriber) {
        self::$eventDispatcher->addSubscriber($subscriber);
      }
    }
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
