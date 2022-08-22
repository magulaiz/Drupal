<?php

/**
 * @file
 * Test section component updates with a layout without third party settings.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Add a layout plugin to an existing entity view display.
$display = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.entity_view_display.node.article.teaser')
  ->execute()
  ->fetchField();
$display = unserialize($display);
$display['dependencies']['module'][] = 'layout_builder';
$display['dependencies']['module'][] = 'layout_discovery';
$display['third_party_settings']['layout_builder']['allow_custom'] = FALSE;
$display['third_party_settings']['layout_builder']['enabled'] = TRUE;
$display['third_party_settings']['layout_builder']['sections'][] = [
  'layout_id' => 'layout_onecol',
  'layout_settings' => ['label' => ''],
  'components' => [
    'f99928d0-fb60-40ed-b8df-9d11b7a2be6e' => [
      'uuid' => 'f99928d0-fb60-40ed-b8df-9d11b7a2be6e',
      'region' => 'content',
      'configuration' => [
        'id' => 'field_block:node:article:title',
      ],
      'weight' => 0,
      'additional' => [],
    ],
  ],
  'third_party_settings' => [],
];
$connection->update('config')
  ->fields(['data' => serialize($display)])
  ->condition('collection', '')
  ->condition('name', 'core.entity_view_display.node.article.teaser')
  ->execute();
