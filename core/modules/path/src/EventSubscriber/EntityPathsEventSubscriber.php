<?php

declare(strict_types=1);

namespace Drupal\path\EventSubscriber;

use Drupal\path\Event\EntityPathsEvent;
use Drupal\path\PathVariant\PathVariant;
use Drupal\path\PathVariant\PlaceHolderInternalPath;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds with internal paths provided by core.
 */
final class EntityPathsEventSubscriber implements EventSubscriberInterface {

  /**
   * Subscriber for canonical internal path.
   */
  public function canonicalInternalPath(EntityPathsEvent $event): void {
    $event->addInternalPath(
      $event->getEntity()->isNew() ? PlaceHolderInternalPath::create() : '/' . $event->getEntity()->toUrl(rel: 'canonical')->getInternalPath(),
      PathVariant::createDefault(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityPathsEvent::class => [
        ['canonicalInternalPath'],
      ],
    ];
  }

}
