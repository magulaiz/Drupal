<?php

namespace Drupal\Core\Mail;

use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

/**
 * Defines a transport factory that creates a transport that includes all of the default and custom factories.
 *
 * @internal
 */
class TransportFactoryManager implements TransportFactoryManagerInterface {

  /**
   * @var \Symfony\Component\Mailer\Transport\TransportFactoryInterface[]
   */
  protected array $factories = [];

  public function addTransportFactory(TransportFactoryInterface $factory): void {
    $this->factories[] = $factory;
  }

  public function getTransportFactories(): array {
    return $this->factories;
  }

}
