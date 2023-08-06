<?php

declare(strict_types = 1);

namespace Drupal\event_test_autoconfigure;

use Drupal\event_test\TestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PrioritizingSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      'ordered_event' => [
        ['minusTen', -10],
        ['plusTen', 10],
      ],
    ];
  }

  public function minusTen(TestEvent $event) {
    $event->report(__METHOD__);
  }

  public function plusTen(TestEvent $event) {
    $event->report(__METHOD__);
  }

}
