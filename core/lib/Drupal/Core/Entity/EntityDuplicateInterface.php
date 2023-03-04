<?php

namespace Drupal\Core\Entity;

/**
 * Defines a common interface for duplicate functionality for entity objects.
 *
 * Due to the backward compatibility, we cannot put duplicate functionality
 * into the EntityInterface, and that's why this interface was introduced.
 * @see https://www.drupal.org/project/drupal/issues/3040556
 *
 * @ingroup entity_api
 */
interface EntityDuplicateInterface {

  /**
   * Changes the values of an entity before it is duplicated.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be duplicated.
   */
  public static function preDuplicate(EntityStorageInterface $storage, EntityInterface $entity);

  /**
   * Acts on a duplicated entity before hooks are invoked.
   *
   * Used after the entity is duplicated, but before saving the entity and
   * before any of the presave hooks are invoked.
   *
   * See the @link entity_crud Entity CRUD topic @endlink for more information.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage object.
   *
   * @see \Drupal\Core\Entity\EntityInterface::createDuplicate()
   */
  public function postDuplicate(EntityStorageInterface $storage);

  /**
   * Determines whether the entity was duplicated from another entity.
   *
   * Usually an entity is a duplicate if there is a duplicate source entity.
   *
   * The duplicate source isn't saved with the entity as a result, this status
   * only persists for the object creation. If the entity is loaded after that,
   * the duplicate source won't be tracked, and the entity won't still be
   * considered a duplicate.
   *
   * @return bool
   *   TRUE if the entity is a duplicate, or FALSE if the entity has already
   *   been saved.
   *
   * @see \Drupal\Core\Entity\EntityInterface::getDuplicateSource()
   */
  public function isDuplicate();

  /**
   * Gets the original entity that was cloned from.
   *
   * The duplicate source isn't saved with the entity as a result, the
   * reference to duplicated source persists only during the object creation.
   * If the entity is loaded after that, the duplicate source won't be tracked.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity this object was cloned from, if the object was cloned as a
   *   duplicate.
   */
  public function getDuplicateSource();

  /**
   * Sets the original entity that was cloned from.
   *
   * @param \Drupal\Core\Entity\EntityInterface $duplicate_source
   *   The entity this object was cloned from, if the object was cloned as a
   *   duplicate.
   */
  public function setDuplicateSource(EntityInterface $duplicate_source);

}
