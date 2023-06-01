<?php

namespace Drupal\Core\Entity\KeyValueStore;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\TranslatableInterface;

/**
 * Provides a key value backend for content entities.
 *
 * @todo Complete the content entity storage implementation.
 *
 * @see https://www.drupal.org/node/2618436.
 */
class KeyValueContentEntityStorage extends KeyValueEntityStorage implements ContentEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function createTranslation(ContentEntityInterface $entity, $langcode, array $values = []) {
    throw new \LogicException('Not implemented yet.');
  }

  /**
   * {@inheritdoc}
   */
  public function hasStoredTranslations(TranslatableInterface $entity) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createRevision(RevisionableInterface $entity, $default = TRUE, $keep_untranslatable_fields = NULL) {
    throw new \LogicException('Not implemented yet.');
  }

  /**
   * {@inheritdoc}
   */
  public function createWithSampleValues($bundle = FALSE, array $values = []) {
    throw new \LogicException('Not implemented yet.');
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleRevisions(array $revision_ids) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestRevisionId($entity_id) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestTranslationAffectedRevisionId($entity_id, $langcode) {
    return NULL;
  }

}
