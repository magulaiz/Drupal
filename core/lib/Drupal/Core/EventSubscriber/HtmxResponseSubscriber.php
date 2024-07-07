<?php

declare(strict_types=1);

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Render\AttachmentsResponseProcessorInterface;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds assets to a header value on responses to HTMX requests.
 *
 * Process all attachments before the attachments are rendered in
 * HtmlResponseSubscriber.
 *
 * @see \Drupal\Core\Ajax\HtmxResponseAttachmentsProcessor
 */
final class HtmxResponseSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a HtmxResponseSubscriber object.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly AttachmentsResponseProcessorInterface $attachmentsProcessor,
  ) {}

  /**
   * Add assemble and attachments add HTMX attributes.
   *
   * @see \Drupal\Core\EventSubscriber\HtmlResponseSubscriber::onRespond
   */
  public function onRespond(ResponseEvent $event): void {
    $response = $event->getResponse();
    $requestHeaders = $this->requestStack->getCurrentRequest()->headers;
    if (!($response instanceof HtmlResponse && $requestHeaders->has('HX-Request'))) {
      // Only operate on HTML responses from an HTMX request.
      return;
    }
    $processedResponse = $this->attachmentsProcessor->processAttachments($response);
    if (!($processedResponse instanceof HtmlResponse)) {
      throw new \TypeError("ResponseEvent::setResponse() requires an HtmlResponse object");
    }
    $event->setResponse($processedResponse);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // Our method needs to run before the HtmlResponseSubscriber(weight 0).
      KernelEvents::RESPONSE => ['onRespond', 100],
    ];
  }

}
