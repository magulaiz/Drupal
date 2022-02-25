<?php

namespace Drupal\Core\Entity\Event;

/**
 * Defines events for core entities.
 */
final class EntityEvents {

  /**
   * Name of the event fired when altering the link of the default revision.
   *
   * @Event
   *
   * @see \Drupal\pathauto\Event\PathautoSkipGenerationEvent
   */
  const ENTITY_LINK_REL_ALTER = 'entity.link_rel_alter';

}
