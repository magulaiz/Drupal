<?php

namespace Drupal\ckeditor5\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A subscriber invalidating cache tags when the default theme changes.
 */
class CKEditor5CacheTag implements EventSubscriberInterface {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a ConfigCacheTag object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->themeHandler = $theme_handler;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Invalidate cache tags when particular system config objects are saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    $config_name = $event->getConfig()->getName();

    // Ckeditor5-stylesheets settings may change when the default theme changes.
    if ($config_name === 'system.theme' && $event->isChanged('default')) {
      $this->cacheTagsInvalidator->invalidateTags(['library_info']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
