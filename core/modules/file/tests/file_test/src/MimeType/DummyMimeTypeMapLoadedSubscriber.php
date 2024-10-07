<?php

declare(strict_types=1);

namespace Drupal\file_test\MimeType;

use Drupal\Core\File\MimeType\MimeTypeMapLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Modifies the MIME type map by adding dummy mappings.
 */
class DummyMimeTypeMapLoadedSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function onMimeTypeMapLoaded(MimeTypeMapLoadedEvent $event): void {
    // Add new mappings.
    $event->map->addMapping('made_up/file_test_1', 'file_test_1');
    $event->map->addMapping('made_up/file_test_2', 'file_test_2');
    $event->map->addMapping('made_up/file_test_2', 'file_test_3');
    // Override existing mapping.
    $event->map->addMapping('made_up/doc', 'doc');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[MimeTypeMapLoadedEvent::class][] = ['onMimeTypeMapLoaded'];

    return $events;
  }

}
