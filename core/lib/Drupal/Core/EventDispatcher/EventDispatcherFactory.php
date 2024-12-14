<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

use Drupal\Core\Database\EventSubscriber\StatementExecutionSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a factory returning the event dispatcher.
 */
class EventDispatcherFactory implements EventDispatcherFactoryInterface {

  /**
   * The event dispatcher singleton.
   */
  private static EventDispatcherInterface $eventDispatcher;

  /**
   * The stage of the request when the singleton was created.
   *
   * @see \Drupal\Core\EventDispatcher\EventDispatcherFactoryStage
   */
  private static EventDispatcherFactoryStage $stage;

  /**
   * Subscribers to be registered for early event dispatcher.
   *
   * Note that the early event dispatcher will be purged during container
   * bootstrap, so any subscriber/listener needed in later stages have to be
   * added also in the service container definition.
   */
  private static array $preBootstrapSubscribers = [
    StatementExecutionSubscriber::class,
  ];

  /**
   * {@inheritdoc}
   */
  public static function createInstance(EventDispatcherFactoryStage|string $stage = EventDispatcherFactoryStage::PreBootstrap): EventDispatcherInterface {
    self::$stage = is_string($stage) ? EventDispatcherFactoryStage::from($stage) : $stage;
    self::$eventDispatcher = new EventDispatcher();
    if (self::$stage !== EventDispatcherFactoryStage::FullContainer) {
      foreach (self::$preBootstrapSubscribers as $subscriber) {
        self::$eventDispatcher->addSubscriber(new $subscriber());
      }
    }
    return self::$eventDispatcher;
  }

  /**
   * Returns the event dispatcher singleton.
   *
   * The singleton is created if it is not yet.
   *
   * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
   *   The event dispatcher.
   */
  private function getInstance(): EventDispatcherInterface {
    if (!isset(self::$eventDispatcher)) {
      return self::createInstance();
    }
    return self::$eventDispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstanceStage(): EventDispatcherFactoryStage {
    if (isset(self::$stage)) {
      return self::$stage;
    }
    throw new \LogicException('The event dispatcher has not been instantiated yet');
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(object $event, ?string $eventName = NULL): object {
    return $this->getInstance()->dispatch($event, $eventName);
  }

  /**
   * {@inheritdoc}
   */
  public function addListener(string $eventName, callable $listener, int $priority = 0): void {
    $this->getInstance()->addListener($eventName, $listener, $priority);
  }

  /**
   * {@inheritdoc}
   */
  public function addSubscriber(EventSubscriberInterface $subscriber): void {
    $this->getInstance()->addSubscriber($subscriber);
  }

  /**
   * {@inheritdoc}
   */
  public function removeListener(string $eventName, callable $listener): void {
    $this->getInstance()->removeListener($eventName, $listener);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSubscriber(EventSubscriberInterface $subscriber): void {
    $this->getInstance()->removeSubscriber($subscriber);
  }

  /**
   * {@inheritdoc}
   */
  public function getListeners(?string $eventName = NULL): array {
    return $this->getInstance()->getListeners($eventName);
  }

  /**
   * {@inheritdoc}
   */
  public function getListenerPriority(string $eventName, callable $listener): ?int {
    return $this->getInstance()->getListenerPriority($eventName, $listener);
  }

  /**
   * {@inheritdoc}
   */
  public function hasListeners(?string $eventName = NULL): bool {
    return $this->getInstance()->hasListeners($eventName);
  }

}
