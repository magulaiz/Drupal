<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Cache\Event\CommonCacheTagsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to collect common cache tags.
 */
class CommonCacheTagsEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      CommonCacheTagsEvent::class => 'onCommonCacheTagsCollect',
    ];
  }

  /**
   * Subscribes to a common cache tags event.
   *
   * @param \Drupal\Core\Cache\Event\CommonCacheTagsEvent $event
   *   The common cache tags event.
   */
  public function onCommonCacheTagsCollect(CommonCacheTagsEvent $event): void {
    $event->addCacheTags([
      'access_policies',
      'CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form',
      'config:core.extension',
      'config:search.settings',
      'config:system.site',
      'config:user.role.anonymous',
      'config:user.role.authenticated',
      'entity_bundles',
      'entity_field_info',
      'entity_types',
      'http_response',
      'library_info',
      'local_task',
      'rendered',
      'route_match',
      'routes',
      'views_data',
    ]);
  }

}
