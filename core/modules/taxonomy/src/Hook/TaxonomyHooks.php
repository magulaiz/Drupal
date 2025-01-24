<?php

namespace Drupal\taxonomy\Hook;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for taxonomy.
 */
class TaxonomyHooks {

  /**
   * Implements hook_local_tasks_alter().
   *
   * @todo Evaluate removing as part of https://www.drupal.org/node/2358923.
   */
  #[Hook('local_tasks_alter')]
  public function localTasksAlter(&$local_tasks): void {
    $local_task_key = 'config_translation.local_tasks:entity.taxonomy_vocabulary.config_translation_overview';
    if (isset($local_tasks[$local_task_key])) {
      // The config_translation module expects the base route to be
      // entity.taxonomy_vocabulary.edit_form like it is for other configuration
      // entities. Taxonomy uses the overview_form as the base route.
      $local_tasks[$local_task_key]['base_route'] = 'entity.taxonomy_vocabulary.overview_form';
    }
  }

  /**
   * Implements hook_entity_operation().
   */
  #[Hook('entity_operation')]
  public function entityOperation(EntityInterface $term) {
    $operations = [];
    if ($term instanceof Term && $term->access('create')) {
      $operations['add-child'] = [
        'title' => t('Add child'),
        'weight' => 10,
        'url' => Url::fromRoute('entity.taxonomy_term.add_form', [
          'taxonomy_vocabulary' => $term->bundle(),
        ], [
          'query' => [
            'parent' => $term->id(),
          ],
        ]),
      ];
    }
    return $operations;
  }

  /**
   * @defgroup taxonomy_index Taxonomy indexing
   * @{
   * Functions to maintain taxonomy indexing.
   *
   * Taxonomy uses default field storage to store canonical relationships
   * between terms and fieldable entities. However its most common use case
   * requires listing all content associated with a term or group of terms
   * sorted by creation date. To avoid slow queries due to joining across
   * multiple node and field tables with various conditions and order by criteria,
   * we maintain a denormalized table with all relationships between terms,
   * published nodes and common sort criteria such as status, sticky and created.
   * When using other field storage engines or alternative methods of
   * denormalizing this data you should set the
   * taxonomy.settings:maintain_index_table to '0' to avoid unnecessary writes in
   * SQL.
   */

  /**
   * Implements hook_ENTITY_TYPE_insert() for node entities.
   */
  #[Hook('node_insert')]
  public function nodeInsert(EntityInterface $node) {
    // Add taxonomy index entries for the node.
    taxonomy_build_node_index($node);
  }

  /**
   * Implements hook_ENTITY_TYPE_update() for node entities.
   */
  #[Hook('node_update')]
  public function nodeUpdate(EntityInterface $node) {
    // If we're not dealing with the default revision of the node, do not make any
    // change to the taxonomy index.
    if (!$node->isDefaultRevision()) {
      return;
    }
    taxonomy_delete_node_index($node);
    taxonomy_build_node_index($node);
  }

  /**
   * Implements hook_ENTITY_TYPE_predelete() for node entities.
   */
  #[Hook('node_predelete')]
  public function nodePredelete(EntityInterface $node) {
    // Clean up the {taxonomy_index} table when nodes are deleted.
    taxonomy_delete_node_index($node);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete() for taxonomy_term entities.
   */
  #[Hook('taxonomy_term_delete')]
  public function taxonomyTermDelete(Term $term) {
    if (\Drupal::config('taxonomy.settings')->get('maintain_index_table')) {
      // Clean up the {taxonomy_index} table when terms are deleted.
      \Drupal::database()->delete('taxonomy_index')->condition('tid', $term->id())->execute();
    }
  }

}
