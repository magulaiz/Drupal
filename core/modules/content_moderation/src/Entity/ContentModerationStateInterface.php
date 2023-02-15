<?php

namespace Drupal\content_moderation\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * An interface for Content moderation state entity.
 *
 * Content moderation state entities track the moderation state of other content
 * entities.
 *
 * @internal
 */
interface ContentModerationStateInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Saves an entity permanently.
   *
   * When saving existing entities, the entity is assumed to be complete,
   * partial updates of entities are not supported.
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public function realSave(): int;

}
