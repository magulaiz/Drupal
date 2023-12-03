<?php

namespace Drupal\Core\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

/**
 * Defines a transport factory that creates a transport that includes all of the default and custom factories.
 *
 * @internal
 */
class TransportFactory {

  /**
   * @var \Symfony\Component\Mailer\Transport\TransportFactoryInterface[]
   */
  protected array $factories = [];

  /**
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger for mailers
   */
  public function __construct(
    protected LoggerInterface $logger,
  ) {}

  /**
   * Returns a Transport that includes all of the default and custom factories.
   */
  public function createTransport(): Transport {
    // Symfony Mailer and Transport classes both optionally depend on the
    // event dispatcher. When provided, a MessageEvent is fired whenever an
    // email is prepared before sending.
    //
    // The MessageEvent will likely play an important role in an upcoming mail
    // API. However, emails handled by this plugin already were processed by
    // hook_mail and hook_mail_alter. Firing the MessageEvent would leak those
    // mails into the code path (i.e., event subscribers) of the new API.
    // Therefore, this plugin deliberately refrains from injecting the event
    // dispatcher.
    $default = Transport::getDefaultFactories(logger: $this->logger);
    return new Transport([...$default, ...$this->factories]);
  }

  public function addTransportFactory(TransportFactoryInterface $factory): void {
    $this->factories[] = $factory;
  }

}
