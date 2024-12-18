<?php

declare(strict_types=1);

namespace Drupal\path\EventSubscriber;

use Drupal\path\Event\PathVariantEvent;
use Drupal\path\PathVariant\PathVariant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds with variants provided by core.
 */
final class PathVariantEventSubscriber implements EventSubscriberInterface {

  /**
   * The default variant.
   */
  public function defaultVariant(PathVariantEvent $event): void {
    $event->addVariant(PathVariant::createDefault());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PathVariantEvent::class => 'defaultVariant',
    ];
  }

}
