<?php

namespace Drupal\taxonomy;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines an interface for vocabulary entity storage classes.
 *
 * @method VocabularyInterface create(array $values = [])
 * @method null|VocabularyInterface load($id)
 * @method null|VocabularyInterface loadRevision($revision_id)
 * @method null|VocabularyInterface loadUnchanged($id)
 * @method VocabularyInterface[] loadMultiple(array $ids = NULL)
 * @method VocabularyInterface[] loadByProperties(array $values = [])
 * @method null|int save(VocabularyInterface $entity)
 * @method void restore(VocabularyInterface $entity)
 */
interface VocabularyStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Gets top-level term IDs of vocabularies.
   *
   * @param array $vids
   *   Array of vocabulary IDs.
   *
   * @return array
   *   Array of top-level term IDs.
   */
  public function getToplevelTids($vids);

}
