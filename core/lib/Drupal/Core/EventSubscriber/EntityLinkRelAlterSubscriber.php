<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Entity\Event\EntityEvents;
use Drupal\Core\Entity\Event\EntityLinkRelAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber alter link rel of entity links.
 */
class EntityLinkRelAlterSubscriber implements EventSubscriberInterface {

  /**
   * Sets the link rel of the default revision to canonical instead of revision.
   *
   * @param \Drupal\Core\Entity\Event\EntityLinkRelAlterEvent $event
   *   The event dispatched in EntityBase::toUrl().
   */
  public function onEntityLinkRelAlter(EntityLinkRelAlterEvent $event) {
    $rel = $event->getRel();
    $entity = $event->getEntity();
    // Links pointing to the current revision point to the actual entity. So
    // instead of using the 'revision' link, use the 'canonical' link.
    if ($entity->isDefaultRevision()) {
      $rel = 'canonical';
    }
    $event->setRel($rel);
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    $events[EntityEvents::ENTITY_LINK_REL_ALTER][] = ['onEntityLinkRelAlter', 99];
    return $events;
  }

}
