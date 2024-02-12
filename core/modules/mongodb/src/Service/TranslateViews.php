<?php

namespace Drupal\mongodb\Service;

/**
 * The MongoDB service for translating SQL queries.
 */
class TranslateViews {

  /**
   * The query translation data.
   */
  const BASE_TABLE_TRANSLATIONS = [
    'comment_field_data' => [
      'base table' => 'comment',
    ],
    'content_moderation_state_field_revision' => [
      'base table' => 'content_moderation_state',
    ],
    'node_field_revision' => [
      'base table' => 'node',
      'revisionable' => TRUE,
    ],
    'node_field_data' => [
      'base table' => 'node',
    ],
    'node__body' => [
      'base table' => 'node',
    ],
    'node_revision' => [
      'base table' => 'node',
    ],
    'node__field_date' => [
      'base table' => 'node',
    ],
    'node__field_link' => [
      'base table' => 'node',
    ],
    'block_content_field_data' => [
      'base table' => 'block_content',
    ],
    'block_content_field_revision' => [
      'base table' => 'block_content',
    ],
    'taxonomy_term_field_data' => [
      'base table' => 'taxonomy_term_data',
    ],
    'taxonomy_term__parent' => [
      'base table' => 'taxonomy_term_data',
    ],
    'users_field_data' => [
      'base table' => 'users',
    ],
    'user__roles' => [
      'base table' => 'users',
    ],
    'user__user_picture' => [
      'base table' => 'users',
    ],
    'user__user_file' => [
      'base table' => 'users',
    ],
    'media_field_data' => [
      'base table' => 'media',
    ],

    // Testing
    'node__field_group_rows' => [
      'base table' => 'node',
    ],
    'node__field_views_testing_group_rows' => [
      'base table' => 'node',
    ],
    'entity_test__field_test' => [
      'base table' => 'entity_test',
    ],
    'entity_test__field_test_data' => [
      'base table' => 'entity_test',
    ],
    'entity_test_mul_changed_property' => [
      'base table' => 'entity_test_mul_changed',
    ],
    'entity_test_rev_revision' => [
      'base table' => 'entity_test_rev',
    ],
    'entity_test_rev__field_test_multiple' => [
      'base table' => 'entity_test_rev',
    ],
    'entity_test_mul_property_data' => [
      'base table' => 'entity_test_mul',
    ],
    'entity_test_mul__field_test' => [
      'base table' => 'entity_test_mul',
    ],
    'entity_test_mul_changed__field_test_data_with_a_long_name' => [
      'base table' => 'entity_test_mul_changed',
    ],
    'entity_test_mul__field_data_test' => [
      'base table' => 'entity_test_mul',
    ],
    'field_data_field_test_list_string' => [
      'base table' => 'node',
    ],
    'field_data_field_test_list_integer' => [
      'base table' => 'node',
    ],
    'entity_test_update_revision' => [
      'base table' => 'entity_test_update',
    ],
    'media_revision' => [
      'base table' => 'media',
    ],
    // From the module "options_test_views"
    'nid' => [
      'base table' => 'node',
    ],

  ];

  /**
   * Translate a table to its MongoDB equivalent.
   */
  public static function baseTable($table) {
    if (isset(self::BASE_TABLE_TRANSLATIONS[$table]['base table'])) {
      return self::BASE_TABLE_TRANSLATIONS[$table]['base table'];
    }
    return $table;
  }

  /**
   * Translate a table to its MongoDB equivalent.
   */
  public static function isRevisionTable($table) {
    if (isset(self::BASE_TABLE_TRANSLATIONS[$table]['revisionable'])) {
      return (bool) self::BASE_TABLE_TRANSLATIONS[$table]['revisionable'];
    }
    return FALSE;
  }

}
