<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\taxonomy\TermStorageInterface;

/**
 * The MongoDB implementation of \Drupal\taxonomy\TermStorage.
 */
class TermStorage extends ContentEntityStorage implements TermStorageInterface {

  use TermStorageTrait;

  /**
   * {@inheritdoc}
   */
  protected function loadTreeData($vid) {
    $this->treeChildren[$vid] = [];
    $this->treeParents[$vid] = [];
    $this->treeTerms[$vid] = [];
    $query = $this->database->select($this->getBaseTable(), 't');
    $result = $query
      ->addTag('taxonomy_term_access')
      ->fields('t', ['tid', 'taxonomy_term_translations'])
      ->condition('taxonomy_term_translations.vid', $vid)
      ->condition('taxonomy_term_translations.default_langcode', TRUE)
      ->orderBy('taxonomy_term_translations.weight')
      ->orderBy('taxonomy_term_translations.name')
      ->execute();
    foreach ($result as $term) {
      $parents = [];
      if (isset($term->taxonomy_term_translations)) {
        foreach ($term->taxonomy_term_translations as $taxonomy_term_translation) {
          if (isset($taxonomy_term_translation['default_langcode']) && ($taxonomy_term_translation['default_langcode'] == 1)) {
            $term->default_langcode = $taxonomy_term_translation['default_langcode'];
            if (isset($taxonomy_term_translation['tid'])) {
              $term->tid = $taxonomy_term_translation['tid'];
            }
            if (isset($taxonomy_term_translation['vid'])) {
              $term->vid = $taxonomy_term_translation['vid'];
            }
            if (isset($taxonomy_term_translation['uuid'])) {
              $term->uuid = $taxonomy_term_translation['uuid'];
            }
            if (isset($taxonomy_term_translation['langcode'])) {
              $term->langcode = $taxonomy_term_translation['langcode'];
            }
            if (isset($taxonomy_term_translation['name'])) {
              $term->name = $taxonomy_term_translation['name'];
            }
            if (isset($taxonomy_term_translation['weight'])) {
              $term->weight = $taxonomy_term_translation['weight'];
            }
            if (isset($taxonomy_term_translation['changed'])) {
              $term->changed = $taxonomy_term_translation['changed'];
            }
            if (isset($taxonomy_term_translation['taxonomy_term_translations__parent']) && is_array($taxonomy_term_translation['taxonomy_term_translations__parent'])) {
              foreach ($taxonomy_term_translation['taxonomy_term_translations__parent'] as $taxonomy_term_translation__parent) {
                if (isset($taxonomy_term_translation__parent['parent_target_id'])) {
                  $parents[] = $taxonomy_term_translation__parent['parent_target_id'];
                }
              }
              $term->taxonomy_term_translations__parent = $taxonomy_term_translation['taxonomy_term_translations__parent'];
            }
            else {
              $parents[] = 0;
            }
          }
        }
        unset($term->taxonomy_term_translations);
      }
      foreach ($parents as $term_parent) {
        $this->treeChildren[$vid][$term_parent][] = $term->tid;
        $this->treeParents[$vid][$term->tid][] = $term_parent;
      }
      $this->treeTerms[$vid][$term->tid] = $term;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadTree($vid, $parent = 0, $max_depth = NULL, $load_entities = FALSE) {
    $cache_key = implode(':', func_get_args());
    if (!isset($this->trees[$cache_key])) {
      // We cache trees, so it's not CPU-intensive to call on a term and its
      // children, too.
      if (!isset($this->treeChildren[$vid])) {
        $this->treeChildren[$vid] = [];
        $this->treeParents[$vid] = [];
        $this->treeTerms[$vid] = [];

        $query = $this->database->select($this->getBaseTable(), 't')
          ->fields('t', ['tid', 'taxonomy_term_current_revision'])
          ->addTag('taxonomy_term_access')
          ->condition('taxonomy_term_current_revision.vid', $vid)
          ->condition('taxonomy_term_current_revision.default_langcode', TRUE)
          ->orderBy('taxonomy_term_current_revision.weight')
          ->orderBy('taxonomy_term_current_revision.name');

        $result = $query->execute()->fetchAll();
        foreach ($result as $term) {
          foreach ($term->taxonomy_term_current_revision as $current_revision) {
            if (is_array($current_revision['taxonomy_term_current_revision__parent'])) {
              foreach ($current_revision['taxonomy_term_current_revision__parent'] as $current_revision_parent) {
                $term->parent = NULL;
                if (isset($current_revision_parent['parent_target_id'])) {
                  $term->parent = $current_revision_parent['parent_target_id'];
                }
                if (!is_null($term->parent)) {
                  $this->treeChildren[$vid][$term->parent][] = $term->tid;
                  $this->treeParents[$vid][$term->tid][] = $term->parent;
                  $this->treeTerms[$vid][$term->tid] = $term;
                }
              }
            }
          }
          unset($term->taxonomy_term_current_revision);
        }
      }

      // Load full entities, if necessary. The entity controller statically
      // caches the results.
      $term_entities = [];
      if ($load_entities) {
        $term_entities = $this->loadMultiple(array_keys($this->treeTerms[$vid]));
      }

      $max_depth = (!isset($max_depth)) ? count($this->treeChildren[$vid]) : $max_depth;
      $tree = [];

      // Keeps track of the parents we have to process, the last entry is used
      // for the next processing step.
      $process_parents = [];
      $process_parents[] = $parent;

      // Loops over the parent terms and adds its children to the tree array.
      // Uses a loop instead of a recursion, because it's more efficient.
      while (count($process_parents)) {
        $parent = array_pop($process_parents);
        // The number of parents determines the current depth.
        $depth = count($process_parents);
        if ($max_depth > $depth && !empty($this->treeChildren[$vid][$parent])) {
          $has_children = FALSE;
          $child = current($this->treeChildren[$vid][$parent]);
          do {
            if (empty($child)) {
              break;
            }
            $term = $load_entities ? $term_entities[$child] : $this->treeTerms[$vid][$child];

            if (isset($this->treeParents[$vid][$load_entities ? $term->id() : $term->tid])) {
              // Clone the term so that the depth attribute remains correct
              // in the event of multiple parents.
              $term = clone $term;
            }
            $term->depth = $depth;
            if (!$load_entities) {
              unset($term->parent);
            }
            $tid = $load_entities ? $term->id() : $term->tid;
            $term->parents = $this->treeParents[$vid][$tid];
            $tree[] = $term;
            if (!empty($this->treeChildren[$vid][$tid])) {
              $has_children = TRUE;

              // We have to continue with this parent later.
              $process_parents[] = $parent;
              // Use the current term as parent for the next iteration.
              $process_parents[] = $tid;

              // Reset pointers for child lists because we step in there more
              // often with multi parents.
              reset($this->treeChildren[$vid][$tid]);
              // Move pointer so that we get the correct term the next time.
              next($this->treeChildren[$vid][$parent]);
              break;
            }
          } while ($child = next($this->treeChildren[$vid][$parent]));

          if (!$has_children) {
            // We processed all terms in this hierarchy-level, reset pointer
            // so that this function works the next time it gets called.
            reset($this->treeChildren[$vid][$parent]);
          }
        }
      }
      $this->trees[$cache_key] = $tree;
    }
    return $this->trees[$cache_key];
  }

  /**
   * {@inheritdoc}
   */
  public function nodeCount($vid) {
    // TODO: There is too little testing for this. Why is there a join in this
    // query. See: \Drupal\Tests\taxonomy\Functional\TokenReplaceTest.
    $query = $this->database->select('taxonomy_index', 'ti');
    $query->addMongodbJoin('LEFT', 'taxonomy_term_data', 'tid', 'taxonomy_index', 'tid', '=', 'td', [['field' => 'taxonomy_term_translations.vid', 'value' => $vid]]);
    $query->addTag('vocabulary_node_count');
    $results = $query->execute()->fetchAll();
    $nids = [];
    foreach ($results as $result) {
      if (isset($result->nid) && !in_array($result->nid, $nids)) {
        $nids[] = $result->nid;
      }
    }
    return count($nids);
  }

  /**
   * {@inheritdoc}
   */
  public function resetWeights($vid) {
    $prefixed_table = $this->database->getMongodbPrefixedTable('taxonomy_term_data');
    $this->database->getConnection()->{$prefixed_table}->updateMany(
      [
        'vid' => $vid,
      ],
      [
        '$set' => [
          'weight' => 0,
          "taxonomy_term_translations.$[translation].weight" => 0,
        ],
      ],
      [
        'arrayFilters' => [
          ["translation.vid" => $vid],
        ],
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeTerms(array $nids, array $vocabs = [], $langcode = NULL) {
    $query = $this->database->select('taxonomy_term_data', 'td');
    foreach ($nids as &$nid) {
      $nid = (int) $nid;
    }
    $extra = [
      [
        'field' => 'nid',
        'value' => $nids,
        'operator' => 'IN',
      ],
    ];
    $query->addMongodbJoin('INNER', 'taxonomy_index', 'tid', 'taxonomy_term_data', 'tid', '=', 'tn', $extra);
    $query->fields('td', ['tid']);
    $query->addField('tn', 'nid', 'node_nid');
    $query->orderby('taxonomy_term_translations.weight');
    $query->orderby('taxonomy_term_translations.name');
    $query->addTag('taxonomy_term_access');
    if (!empty($vocabs)) {
      $query->condition('taxonomy_term_translations.vid', $vocabs, 'IN');
    }
    if (!empty($langcode)) {
      $query->condition('taxonomy_term_translations.langcode', $langcode);
    }

    $results = [];
    $all_tids = [];
    foreach ($query->execute() as $term_record) {
      if (isset($term_record->tn_node_nid) && is_array($term_record->tn_node_nid)) {
        foreach ($term_record->tn_node_nid as $node_nid) {
          $results[$node_nid][] = $term_record->tid;
        }
      }
      $all_tids[] = $term_record->tid;
    }

    $all_terms = $this->loadMultiple($all_tids);
    $terms = [];
    foreach ($results as $nid => $tids) {
      foreach ($tids as $tid) {
        $terms[$nid][$tid] = $all_terms[$tid];
      }
    }
    return $terms;
  }

  /**
   * {@inheritdoc}
   */
  public function getVocabularyHierarchyType($vid) {
    // @TODO Needs work!

    // Return early if we already computed this value.
    if (isset($this->vocabularyHierarchyType[$vid])) {
      return $this->vocabularyHierarchyType[$vid];
    }

    $parent_field_storage = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId)['parent'];
    $table_mapping = $this->getTableMapping();

    $target_id_column = $table_mapping->getFieldColumnName($parent_field_storage, 'target_id');
    $delta_column = $table_mapping->getFieldColumnName($parent_field_storage, TableMappingInterface::DELTA);

    $query = $this->database->select($table_mapping->getFieldTableName('parent'), 'p');
    $query->addExpression("MAX([$target_id_column])", 'max_parent_id');
    $query->addExpression("MAX([$delta_column])", 'max_delta');
    $query->condition('bundle', $vid);

    $result = $query->execute()->fetchAll();

    // If all the terms have the same parent, the parent can only be root (0).
    if ((int) $result[0]->max_parent_id === 0) {
      $this->vocabularyHierarchyType[$vid] = VocabularyInterface::HIERARCHY_DISABLED;
    }
    // If no term has a delta higher than 0, no term has multiple parents.
    elseif ((int) $result[0]->max_delta === 0) {
      $this->vocabularyHierarchyType[$vid] = VocabularyInterface::HIERARCHY_SINGLE;
    }
    else {
      $this->vocabularyHierarchyType[$vid] = VocabularyInterface::HIERARCHY_MULTIPLE;
    }

    return $this->vocabularyHierarchyType[$vid];
  }

  /**
   * {@inheritdoc}
   */
  public function getTermIdsWithPendingRevisions() {
    $table_mapping = $this->getTableMapping();
    $id_field = $table_mapping->getColumnNames($this->entityType->getKey('id'))['value'];
    $revision_field = $table_mapping->getColumnNames($this->entityType->getKey('revision'))['value'];
    $rta_field = $table_mapping->getColumnNames($this->entityType->getKey('revision_translation_affected'))['value'];
    $revision_default_field = $table_mapping->getColumnNames($this->entityType->getRevisionMetadataKey('revision_default'))['value'];
    $latest_revision_table = $this->getLatestRevisionTable();

    $results = $this->database->select($this->getBaseTable(), 't')
      ->fields('t', [$id_field, $latest_revision_table])
      ->execute()
      ->fetchAll();

    $term_ids_with_pending_revisions = [];
    foreach ($results as $result) {
      $latest_revision = $result->{$latest_revision_table};
      $revision_id = NULL;
      foreach ($latest_revision as $latest_revision_language) {
        if (($latest_revision_language[$rta_field] === TRUE) && ($latest_revision_language[$revision_default_field] === FALSE)) {
          $revision_id = $latest_revision_language[$revision_field];
        }
      }
      if (!is_null($revision_id)) {
        $term_ids_with_pending_revisions[$result->{$id_field}] = $revision_id;
      }
    }

    return $term_ids_with_pending_revisions;
  }

}
