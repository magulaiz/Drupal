<?php

namespace Drupal\Core\Mail;

/**
 * Defines an interface for retrieving custom transport interfaces.
 *
 * @internal
 */
interface TransportFactoryManagerInterface {

  /**
   * @return \Symfony\Component\Mailer\Transport\TransportFactoryInterface[]
   */
  public function getTransportFactories(): array;

}
