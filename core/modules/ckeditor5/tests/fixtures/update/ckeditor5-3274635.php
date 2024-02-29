<?php

/**
 * @file
 * Test fixture.
 */

use Drupal\Core\Database\Database;
use Drupal\Core\Serialization\Yaml;

$connection = Database::getConnection();

$format_list_ol_type = Yaml::decode(file_get_contents(__DIR__ . '/filter.format.test_format_list_ol_type.yml'));
$format_list_no_type = Yaml::decode(file_get_contents(__DIR__ . '/filter.format.test_format_list_no_type.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'filter.format.test_format_list_ol_type',
    'data' => serialize($format_list_ol_type),
  ])
  ->values([
    'collection' => '',
    'name' => 'filter.format.test_format_list_no_type',
    'data' => serialize($format_list_no_type),
  ])
  ->execute();

$editor_list_ol_type = Yaml::decode(file_get_contents(__DIR__ . '/editor.editor.test_format_list_ol_type.yml'));
$editor_list_no_type = Yaml::decode(file_get_contents(__DIR__ . '/editor.editor.test_format_list_no_type.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'editor.editor.test_format_list_ol_type',
    'data' => serialize($editor_list_ol_type),
  ])
  ->values([
    'collection' => '',
    'name' => 'editor.editor.test_format_list_no_type',
    'data' => serialize($editor_list_no_type),
  ])
  ->execute();
