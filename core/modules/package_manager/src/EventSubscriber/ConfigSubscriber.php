<?php

declare(strict_types=1);

namespace Drupal\package_manager\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to remove executable paths from exported config.
 *
 * @internal
 *   This is an internal part of Package Manager and may be changed or removed
 *   at any time without warning. External code should not interact with this
 *   class.
 */
final class ConfigSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::STORAGE_TRANSFORM_EXPORT => 'onExport',
    ];
  }

  /**
   * Removes executable paths from exported config.
   *
   * @param \Drupal\Core\Config\StorageTransformEvent $event
   *   The event object.
   */
  public function onExport(StorageTransformEvent $event): void {
    $storage = $event->getStorage();

    $settings = $storage->read('package_manager.settings');
    $settings['executables'] = [
      'composer' => NULL,
      'rsync' => NULL,
    ];
    $storage->write('package_manager.settings', $settings);
  }

}
