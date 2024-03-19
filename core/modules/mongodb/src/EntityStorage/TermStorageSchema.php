<?php

namespace Drupal\mongodb\EntityStorage;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * The MongoDB implementation of \Drupal\taxonomy\TermStorageSchema.
 */
class TermStorageSchema extends ContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset = FALSE);

    $schema['taxonomy_index'] = [
      'description' => 'Maintains denormalized information about node/term relationships.',
      'fields' => [
        'nid' => [
          'description' => 'The {node}.nid this record tracks.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ],
        'tid' => [
          'description' => 'The term ID.',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ],
        'status' => [
          'description' => 'Boolean indicating whether the node is published (visible to non-administrators).',
          'type' => 'bool',
          'not null' => TRUE,
          'default' => 1,
        ],
        'sticky' => [
          'description' => 'Boolean indicating whether the node is sticky.',
          'type' => 'bool',
          'not null' => FALSE,
          'default' => 0,
          'size' => 'tiny',
        ],
        'created' => [
          'description' => 'The time when the node was created.',
          'type' => 'date',
          'not null' => TRUE,
          'default' => 0,
        ],
      ],
      'primary key' => ['nid', 'tid'],
      'indexes' => [
        'term_node' => ['tid', 'status', 'sticky', 'created'],
      ],
      'foreign keys' => [
        'tracked_node' => [
          'table' => 'node',
          'columns' => ['nid' => 'nid'],
        ],
        'term' => [
          'table' => 'taxonomy_term_data',
          'columns' => ['tid' => 'tid'],
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);

    // Added by MongoDB.
    if ($table_name == 'taxonomy_term_current_revision') {
      $schema['primary key'] = ['tid', 'vid', 'langcode'];
    }

    return $schema;
  }

}
