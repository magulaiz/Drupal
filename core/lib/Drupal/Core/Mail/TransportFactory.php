<?php

namespace Drupal\Core\Mail;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;
use Symfony\Component\Mailer\Transport;

class TransportFactory {

    /**
     * @param LoggerInterface $logger
     * @param TransportFactoryInterface[] $factories
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected iterable $factories,
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
}
