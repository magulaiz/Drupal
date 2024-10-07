<?php

namespace Drupal\Core\File\Event;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\MimeType\DefaultMimeTypeMap;
use Drupal\Core\File\MimeType\MimeTypeMapLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Modifies the MIME type map by calling hook_file_mimetype_mapping_alter().
 */
class LegacyMimeTypeMapLoadedSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected readonly ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Handle the event by calling deprecated hook_file_mimetype_mapping_alter().
   */
  public function onMimeTypeMapLoaded(MimeTypeMapLoadedEvent $event): void {
    if (!$event->map instanceof DefaultMimeTypeMap) {
      return;
    }
    // @phpstan-ignore-next-line method.deprecated
    $mapping = $event->map->getMapping();
    $this->moduleHandler->alterDeprecated(
      'This hook is deprecated in drupal:11.1.0 and will be removed before drupal:12.0.0. Implement a MimeTypeMapLoadedEvent listener instead. See https://www.drupal.org/node/2311679',
      'file_mimetype_mapping',
      $mapping,
    );
    // @phpstan-ignore-next-line method.deprecated
    $event->map->setMapping($mapping);
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
