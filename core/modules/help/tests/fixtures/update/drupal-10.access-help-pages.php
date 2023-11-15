<?php

/**
 * @file
 * Contains database additions for testing the help module permission.
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$role = Yaml::decode(file_get_contents(__DIR__ . '/drupal-10.access-help-pages.yml'));
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'user.role.content_editor',
    'data' => serialize($role),
  ])
  ->execute();
