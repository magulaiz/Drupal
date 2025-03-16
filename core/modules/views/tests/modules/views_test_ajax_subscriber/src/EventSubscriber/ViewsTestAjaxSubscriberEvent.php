<?php

namespace Drupal\views_test_ajax_subscriber\EventSubscriber;

use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ViewsTestAjaxSubscriberEvent.
 *
 * @package Drupal\views_test_ajax_subscriber
 */
class ViewsTestAjaxSubscriberEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }

  /**
   * This method is called whenever the kernel.response event is
   * dispatched.
   *
   * @param Event $event
   * @throws \Drupal\views_test_ajax_subscriber\EventSubscriber\UnexpectedViewAjaxException
   */
  public function onResponse(Event $event) {
    /** @var ViewAjaxResponse $response */
    $response = $event->getResponse();

    // Only alter views ajax responses.
    if (!($response instanceof ViewAjaxResponse)) {
      return;
    }

    $expectedView = \Drupal::state()->get('views_test_ajax_subscriber');
    if ($expectedView && $response->getView()->id() != $expectedView) {
      throw new UnexpectedViewAjaxException("Expected: {$expectedView}. Got: {$response->getView()->id()}.");
    }
  }

}

class UnexpectedViewAjaxException extends \Exception {}
