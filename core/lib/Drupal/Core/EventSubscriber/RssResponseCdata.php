<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Component\Utility\Xss;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to wrap RSS descriptions in CDATA.
 */
class RssResponseCdata implements EventSubscriberInterface {

  /**
   * Wraps rss descriptions in CDATA.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event.
   */
  public function onResponse(ResponseEvent $event) {
    // Only care about RSS responses.
    if (stripos($event->getResponse()->headers->get('Content-Type', ''), 'application/rss+xml') === FALSE) {
      return;
    }

    $response = $event->getResponse();
    $response->setContent($this->wrapDescriptionCdata($response->getContent(), $event->getRequest()));
  }

  /**
   * Converts description node to CDATA RSS markup.
   *
   * @param string $rss_markup
   *   The RSS markup to update.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return string
   *   The updated RSS xml.
   */
  protected function wrapDescriptionCdata($rss_markup, Request $request) {
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

    foreach ($rss_dom->getElementsByTagName('item') as $item) {
      foreach ($item->getElementsByTagName('description') as $node) {
        $html_markup = $node->nodeValue;
        if (!empty($html_markup)) {
          $html_markup = Xss::filter($html_markup, ['a', 'abbr', 'acronym', 'address', 'b', 'bdo', 'big', 'blockquote', 'br', 'caption', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dl', 'dt', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 'q', 'samp', 'small', 'span', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'ul', 'var']);
          $new_node = $rss_dom->createCDATASection($html_markup);
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
    // Should run after any other response subscriber that modifies the markup.
    // @see \Drupal\Core\EventSubscriber\RssResponseRelativeUrlFilter
    $events[KernelEvents::RESPONSE][] = ['onResponse', -512];

    return $events;
  }

}
