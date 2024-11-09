<?php

declare(strict_types=1);

namespace Drupal\event_listener_test\EventSubscriber;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Test class for event listeners annotated by AsEventListener attribute.
 */
#[AsEventListener]
class TestAsEventListenerListener {

  public function __construct(protected readonly StateInterface $state) {}

  public function __invoke(Event $event): void {
    $this->state->set('event_listener_test_invoke', TRUE);
  }

  #[AsEventListener]
  public function onEvent(Event $event): void {
    $this->state->set('event_listener_test_on_event', TRUE);
  }

}
