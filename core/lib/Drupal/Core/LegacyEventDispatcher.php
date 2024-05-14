<?php

namespace Drupal\Core;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provide an alias for services expecting a ContainerAwareEventDispatcher.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0.
 *   Use \Symfony\Contracts\EventDispatcher\EventDispatcherInterface instead.
 *
 * @see https://www.drupal.org/node/3376090
 */
class_alias(EventDispatcherInterface::class, 'Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher', FALSE);

/**
 * Decorates the event dispatcher in order to add a class alias.
 *
 * @internal
 *   This class exists to bridge classes using
 *   \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher as a
 *   type-hint to \Symfony\Component\EventDispatcher\EventDispatcherInterface.
 */
final class LegacyEventDispatcher implements EventDispatcherInterface {

  public function __construct(protected EventDispatcherInterface $inner) {
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(object $event, ?string $eventName = NULL): object {
    return $this->inner->dispatch($event, $eventName);
  }

  /**
   * {@inheritdoc}
   */
  public function addListener(string $eventName, callable|array $listener, int $priority = 0) {
    $this->inner->addListener($eventName, $listener, $priority);
  }

  /**
   * {@inheritdoc}
   */
  public function addSubscriber(EventSubscriberInterface $subscriber) {
    $this->inner->addSubscriber($subscriber);
  }

  /**
   * {@inheritdoc}
   */
  public function removeListener(string $eventName, callable|array $listener) {
    $this->inner->removeListener($eventName, $listener);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSubscriber(EventSubscriberInterface $subscriber) {
    $this->inner->removeSubscriber($subscriber);
  }

  /**
   * {@inheritdoc}
   */
  public function getListeners(?string $eventName = NULL): array {
    return $this->inner->getListeners($eventName);
  }

  /**
   * {@inheritdoc}
   */
  public function getListenerPriority(string $eventName, callable|array $listener): ?int {
    return $this->inner->getListenerPriority($eventName, $listener);
  }

  /**
   * {@inheritdoc}
   */
  public function hasListeners(?string $eventName = NULL): bool {
    return $this->inner->hasListeners($eventName);
  }

}
