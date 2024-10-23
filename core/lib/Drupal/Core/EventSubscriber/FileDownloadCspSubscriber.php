<?php

namespace Drupal\Core\EventSubscriber;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to add Content-Security-Policy header to file downloads.
 */
class FileDownloadCspSubscriber implements EventSubscriberInterface {

  /**
   * Sets Content-Security-Policy on file downloads.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();

    if (!($response instanceof BinaryFileResponse)) {
      return;
    }

    // If no Content-Security-Policy header has been set, add a default.
    if (!$response->headers->has('Content-Security-Policy')) {
      if ($response->headers->get('Content-Type') == 'image/svg+xml') {
        $response->headers->set('Content-Security-Policy', "default-src 'none'; img-src data:; style-src 'unsafe-inline'", FALSE);
      }
      else {
        $response->headers->set('Content-Security-Policy', "default-src 'none'", FALSE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
