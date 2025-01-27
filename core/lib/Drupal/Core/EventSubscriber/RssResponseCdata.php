<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to wrap RSS descriptions in CDATA.
 */
class RssResponseCdata implements EventSubscriberInterface {

  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
    protected readonly RendererInterface $renderer,
  ) {}

  /**
   * Wraps RSS descriptions in CDATA.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event.
   */
  public function onResponse(ResponseEvent $event): void {
    // Skip responses that are not RSS.
    if (stripos($event->getResponse()->headers->get('Content-Type', ''), 'application/rss+xml') === FALSE) {
      return;
    }

    $response = $event->getResponse();
    $response->setContent($this->wrapDescriptionCdata($response->getContent()));
  }

  /**
   * Converts description node to CDATA RSS markup.
   *
   * @param string $rss_markup
   *   The RSS markup to update.
   *
   * @return string|false
   *   The updated RSS XML or FALSE if there is an error saving the xml.
   */
  protected function wrapDescriptionCdata(string $rss_markup): string|false {
    $rss_dom = new \DOMDocument();

    // Load the RSS, if there are parsing errors, abort and return the unchanged
    // markup.
    $previous_value = libxml_use_internal_errors(TRUE);
    $rss_dom->loadXML($rss_markup);
    $errors = libxml_get_errors();
    libxml_use_internal_errors($previous_value);
    if ($errors) {
      return $rss_markup;
    }
    $config = $this->configFactory->get('system.rss');
    $format = $config->get('items.text_format') ?: 'basic_html';

    foreach ($rss_dom->getElementsByTagName('item') as $item) {
      foreach ($item->getElementsByTagName('description') as $node) {
        $html_markup = $node->nodeValue;
        if (!empty($html_markup)) {
          $processed = [
            '#type' => 'processed_text',
            '#text' => $html_markup,
            '#format' => $format,
          ];
          $markup = $this->renderer->renderInIsolation($processed);
          $new_node = $rss_dom->createCDATASection($markup);
          $node->replaceChild($new_node, $node->firstChild);
        }
      }
    }

    return $rss_dom->saveXML();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // This should run after any other response subscriber that modifies the
    // markup.
    // @see \Drupal\Core\EventSubscriber\RssResponseRelativeUrlFilter
    $events[KernelEvents::RESPONSE][] = ['onResponse', -513];

    return $events;
  }

}
