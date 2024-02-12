<?php

namespace Drupal\mongodb\modules\path_alias;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\path_alias\AliasRepository as CoreAliasRepository;

/**
 * Provides the default path alias lookup operations.
 */
class AliasRepository extends CoreAliasRepository {

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode) {
    // @todo There is no testing for this method.
    $select = $this->getBaseQuery()
      ->fields('base_table', ['path_alias_current_revision']);

    if (!empty($preloaded)) {
      $conditions = $this->connection->condition('OR');
      foreach ($preloaded as $preloaded_item) {
        $conditions->condition('path_alias_current_revision.path', $this->connection->escapeLike($preloaded_item), 'LIKE');
      }
      $select->condition($conditions);
    }

    $this->addLanguageFallback($select, $langcode);

    // We order by ID ASC so that fetchAllKeyed() returns the most recently
    // created alias for each source. Subsequent queries using fetchField() must
    // use ID DESC to have the same effect.
    $select->orderBy('id', 'ASC');

    $result = $select->execute()->fetchAllKeyed();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupBySystemPath($path, $langcode) {
    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->getBaseQuery()
      ->fields('base_table', ['id', 'path_alias_current_revision'])
      ->condition('path_alias_current_revision.path', $this->connection->escapeLike($path), 'LIKE');

    $this->addLanguageFallback($select, $langcode);

    $select->orderBy('id', 'DESC');

    $result = $select->execute()->fetchAssoc() ?: NULL;

    if (isset($result['path_alias_current_revision'])) {
      $revision = NULL;
      foreach ($result['path_alias_current_revision'] as $current_revision) {
        // @todo We need extra testing for when both the langcode revision and the
        // "und" revision is available.
        if (in_array($current_revision['langcode'], [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED], TRUE)) {
          $revision = $current_revision;
        }
      }
      $result['path'] = $revision['path'] ?? '';
      $result['alias'] = $revision['alias'] ?? '';
      $result['langcode'] = $revision['langcode'] ?? '';
      unset($result['path_alias_current_revision']);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function lookupByAlias($alias, $langcode) {
    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->getBaseQuery()
      ->fields('base_table', ['id', 'path_alias_current_revision'])
      ->condition('path_alias_current_revision.alias', $this->connection->escapeLike($alias), 'LIKE');

    $this->addLanguageFallback($select, $langcode);

    $select->orderBy('id', 'DESC');

    $result = $select->execute()->fetchAssoc() ?: NULL;

    if (isset($result['path_alias_current_revision'])) {
      $revision = NULL;
      foreach ($result['path_alias_current_revision'] as $current_revision) {
        // @todo We need extra testing for when both the langcode revision and the
        // "und" revision is available.
        if (in_array($current_revision['langcode'], [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED], TRUE)) {
          $revision = $current_revision;
        }
      }
      $result['path'] = $revision['path'] ?? '';
      $result['alias'] = $revision['alias'] ?? '';
      $result['langcode'] = $revision['langcode'] ?? '';
      unset($result['path_alias_current_revision']);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function pathHasMatchingAlias($initial_substring) {
    $query = $this->getBaseQuery();
    $query->addExpression(1);

    return (bool) $query
      ->condition('path_alias_current_revision.path', $this->connection->escapeLike($initial_substring) . '%', 'LIKE')
      ->range(0, 1)
      ->execute()
      ->fetchField();
  }

  /**
   * Returns a SELECT query for the path_alias base table.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   A Select query object.
   */
  protected function getBaseQuery() {
    $query = $this->connection->select('path_alias', 'base_table');
    $query->condition('path_alias_current_revision.status', TRUE);

    return $query;
  }

  /**
   * Adds path alias language fallback conditions to a select query object.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   A Select query object.
   * @param string $langcode
   *   Language code to search the path with. If there's no path defined for
   *   that language it will search paths without language.
   */
  protected function addLanguageFallback(SelectInterface $query, $langcode) {
    // Always get the language-specific alias before the language-neutral one.
    // For example 'de' is less than 'und' so the order needs to be ASC, while
    // 'xx-lolspeak' is more than 'und' so the order needs to be DESC.
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    if ($langcode === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $query->orderBy('path_alias_current_revision.langcode', 'DESC');
    }
    else {
      $query->orderBy('path_alias_current_revision.langcode', 'ASC');
    }
    $query->condition('path_alias_current_revision.langcode', $langcode_list, 'IN');
  }

}
