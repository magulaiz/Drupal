<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\DestructableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Destructs services that are initiated and tagged with "needs_destruction".
 *
 * @see \Drupal\Core\DestructableInterface
 */
class KernelDestructionSubscriber implements EventSubscriberInterface {

  /**
   * An array of services that require destruction.
   */
  protected array $services = [];

  /**
   * Registers a service for destruction.
   *
   * Calls to this configurator method are set up in
   * RegisterServicesForDestructionPass::process().
   *
   * @param \Drupal\Core\DestructableInterface $service
   *   The service to be destructed.
   */
  public function registerService(DestructableInterface $service) {
    $this->services[] = $service;
  }

  /**
   * Invoked by the terminate kernel event.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event object.
   */
  public function onKernelTerminate(TerminateEvent $event) {
    foreach ($this->services as $service) {
      $service->destruct();
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 100];
    return $events;
  }

}
