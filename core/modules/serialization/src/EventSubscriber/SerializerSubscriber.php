<?php

namespace Drupal\serialization\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Event subscriber for adding additional content types to the request.
 */
class SerializerSubscriber implements EventSubscriberInterface {

  /**
   * Register content type formats on the request object.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   An event object.
   */
  public function onKernelRequest(RequestEvent $event) {
    $event->getRequest()->setFormat('yaml', ['application/yaml', 'text/yaml']);
  }

  /**
   * Setup response object on exception.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The Events to process.
   */
  public function onKernelException(ExceptionEvent $event) {
    if ($event->getThrowable() instanceof ParseException) {
      $event->setResponse(new Response('Yaml Parse Error', 400));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // To process REST POST endpoints with YAML mime type.
    // Priority 40 is to make sure this event callback is called just before
    // \Symfony\Component\HttpKernel\EventListener\RouterListener::
    // onKernelRequest().
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 40];
    $events[KernelEvents::EXCEPTION][] = ['onKernelException'];

    return $events;
  }

}
