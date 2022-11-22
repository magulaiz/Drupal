<?php

namespace Drupal\Core\Entity;

/**
 * A storage that supports content entity types.
 *
 * @method \Drupal\Core\Entity\ContentEntityInterface create(array $values = [])
 * @method null|\Drupal\Core\Entity\ContentEntityInterface load($id)
 * @method null|\Drupal\Core\Entity\ContentEntityInterface loadRevision($revision_id)
 * @method null|\Drupal\Core\Entity\ContentEntityInterface loadUnchanged($id)
 * @method \Drupal\Core\Entity\ContentEntityInterface[] loadMultiple(array $ids = NULL)
 * @method \Drupal\Core\Entity\ContentEntityInterface[] loadByProperties(array $values = [])
 * @method null|int save(\Drupal\Core\Entity\ContentEntityInterface $entity)
 * @method void restore(\Drupal\Core\Entity\ContentEntityInterface $entity)
 */
interface ContentEntityStorageInterface extends EntityStorageInterface, TranslatableRevisionableStorageInterface {

  /**
   * Creates an entity with sample field values.
   *
   * @param string|bool $bundle
   *   (optional) The entity bundle.
   * @param array $values
   *   (optional) Any default values to use during generation.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   *   A fieldable content entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown if the bundle does not exist or was needed but not specified.
   */
  public function createWithSampleValues($bundle = FALSE, array $values = []);

}
