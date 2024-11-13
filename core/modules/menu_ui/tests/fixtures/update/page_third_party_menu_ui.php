<?php
// phpcs:ignoreFile

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Create third_party.menu_ui settings in node.type.page.
$node_type_page_config = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'node.type.page')
  ->execute()
  ->fetchField();
$node_type_page_config = unserialize($node_type_page_config);
$node_type_page_config['third_party_settings']['menu_ui'] = [];
$connection->update('config')
  ->fields(['data' => serialize($node_type_page_config)])
  ->condition('collection', '')
  ->condition('name', 'node.type.page')
  ->execute();
