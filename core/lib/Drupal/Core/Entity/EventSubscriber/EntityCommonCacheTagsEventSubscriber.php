<?php

namespace Drupal\Core\Entity\EventSubscriber;

use Drupal\Core\Cache\Event\CommonCacheTagsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to collect common cache tags for entity types.
 */
class EntityCommonCacheTagsEventSubscriber implements EventSubscriberInterface {

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
    // @todo Dynamically populate this.
    $event->addCacheTags([
      'block_content_view',
      'block_view',
      'node_list',
      'node_values',
      'node_view',
      'taxonomy_term_list',
      'user_values',
      'user_view',
    ]);
  }

}
