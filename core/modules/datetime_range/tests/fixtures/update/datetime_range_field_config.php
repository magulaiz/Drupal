<?php

/**
 * @file
 * Contains database additions for testing the upgrade path datetime_range_post_update_add_optional_end_date().
 */

use Drupal\Core\Database\Database;
use Drupal\Core\Serialization\Yaml;
use Drupal\field\Entity\FieldStorageConfig;

$connection = Database::getConnection();

// Add all datetime_range_removed_post_updates() as existing updates.
require_once __DIR__ . '/../../../../datetime_range/datetime_range.post_update.php';
$existing_updates = $connection->select('key_value')
  ->fields('key_value', ['value'])
  ->condition('collection', 'post_update')
  ->condition('name', 'existing_updates')
  ->execute()
  ->fetchField();
$existing_updates = unserialize($existing_updates);
$existing_updates = array_merge(
  $existing_updates,
  array_keys(datetime_range_removed_post_updates())
);
$connection->update('key_value')
  ->fields(['value' => serialize($existing_updates)])
  ->condition('collection', 'post_update')
  ->condition('name', 'existing_updates')
  ->execute();

// Configuration for the datetime_range field storage.
$field_storage_datetime_range = Yaml::decode(file_get_contents(__DIR__ . '/field.storage.node.field_date_range.yml'));
$field_datetime_range = Yaml::decode(file_get_contents(__DIR__ . '/field.field.node.page.field_date_range.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'field.storage.' . $field_storage_datetime_range['id'],
    'data' => serialize($field_storage_datetime_range),
  ])
  ->execute();

$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'field.field.' . $field_datetime_range['id'],
    'data' => serialize($field_datetime_range),
  ])
  ->execute();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
$extensions['module']['datetime_range'] = 0;
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
  ])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

$connection->insert('key_value')
  ->fields([
    'collection',
    'name',
    'value',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'node.field_schema_data.field_date_range',
    'value' => 'a:2:{s:22:"node__field_date_range";a:4:{s:11:"description";s:45:"Data storage for node field field_date_range.";s:6:"fields";a:8:{s:6:"bundle";a:5:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:8:"not null";b:1;s:7:"default";s:0:"";s:11:"description";s:88:"The field instance bundle to which this row belongs, used when deleting a field instance";}s:7:"deleted";a:5:{s:4:"type";s:3:"int";s:4:"size";s:4:"tiny";s:8:"not null";b:1;s:7:"default";i:0;s:11:"description";s:60:"A boolean indicating whether this data item has been deleted";}s:9:"entity_id";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:38:"The entity id this data is attached to";}s:11:"revision_id";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:47:"The entity revision id this data is attached to";}s:8:"langcode";a:5:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:32;s:8:"not null";b:1;s:7:"default";s:0:"";s:11:"description";s:37:"The language code for this data item.";}s:5:"delta";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:67:"The sequence number for this data item, used for multi-value fields";}s:22:"field_date_range_value";a:4:{s:11:"description";s:21:"The start date value.";s:4:"type";s:7:"varchar";s:6:"length";i:20;s:8:"not null";b:1;}s:26:"field_date_range_end_value";a:4:{s:11:"description";s:19:"The end date value.";s:4:"type";s:7:"varchar";s:6:"length";i:20;s:8:"not null";b:0;}}s:11:"primary key";a:4:{i:0;s:9:"entity_id";i:1;s:7:"deleted";i:2;s:5:"delta";i:3;s:8:"langcode";}s:7:"indexes";a:4:{s:6:"bundle";a:1:{i:0;s:6:"bundle";}s:11:"revision_id";a:1:{i:0;s:11:"revision_id";}s:22:"field_date_range_value";a:1:{i:0;s:22:"field_date_range_value";}s:26:"field_date_range_end_value";a:1:{i:0;s:26:"field_date_range_end_value";}}}s:31:"node_revision__field_date_range";a:4:{s:11:"description";s:57:"Revision archive storage for node field field_date_range.";s:6:"fields";a:8:{s:6:"bundle";a:5:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:8:"not null";b:1;s:7:"default";s:0:"";s:11:"description";s:88:"The field instance bundle to which this row belongs, used when deleting a field instance";}s:7:"deleted";a:5:{s:4:"type";s:3:"int";s:4:"size";s:4:"tiny";s:8:"not null";b:1;s:7:"default";i:0;s:11:"description";s:60:"A boolean indicating whether this data item has been deleted";}s:9:"entity_id";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:38:"The entity id this data is attached to";}s:11:"revision_id";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:47:"The entity revision id this data is attached to";}s:8:"langcode";a:5:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:32;s:8:"not null";b:1;s:7:"default";s:0:"";s:11:"description";s:37:"The language code for this data item.";}s:5:"delta";a:4:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:8:"not null";b:1;s:11:"description";s:67:"The sequence number for this data item, used for multi-value fields";}s:22:"field_date_range_value";a:4:{s:11:"description";s:21:"The start date value.";s:4:"type";s:7:"varchar";s:6:"length";i:20;s:8:"not null";b:1;}s:26:"field_date_range_end_value";a:4:{s:11:"description";s:19:"The end date value.";s:4:"type";s:7:"varchar";s:6:"length";i:20;s:8:"not null";b:0;}}s:11:"primary key";a:5:{i:0;s:9:"entity_id";i:1;s:11:"revision_id";i:2;s:7:"deleted";i:3;s:5:"delta";i:4;s:8:"langcode";}s:7:"indexes";a:4:{s:6:"bundle";a:1:{i:0;s:6:"bundle";}s:11:"revision_id";a:1:{i:0;s:11:"revision_id";}s:22:"field_date_range_value";a:1:{i:0;s:22:"field_date_range_value";}s:26:"field_date_range_end_value";a:1:{i:0;s:26:"field_date_range_end_value";}}}}',
  ])
  ->execute();

$data = $connection->select('key_value')
  ->fields('key_value', ['value'])
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'node.field_storage_definitions')
  ->execute()
  ->fetchField();
$data = unserialize($data);
$data['field_date_range'] = new FieldStorageConfig($field_storage_datetime_range);
$connection->update('key_value')
  ->fields(['value' => serialize($data)])
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'node.field_storage_definitions')
  ->execute();
