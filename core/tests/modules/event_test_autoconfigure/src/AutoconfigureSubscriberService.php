<?php

declare(strict_types = 1);

namespace Drupal\event_test_autoconfigure;

use Drupal\event_test\TestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoconfigureSubscriberService implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      TestEvent::class => 'testMethod',
    ];
  }

  public function testMethod(TestEvent $event): void {
    $event->report(__METHOD__);
  }

}
