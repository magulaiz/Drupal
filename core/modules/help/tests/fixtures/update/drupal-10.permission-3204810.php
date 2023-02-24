<?php

/**
 * @file
 * Contains database additions to for testing upgrade path for help permission.
 *
 * @see https://www.drupal.org/node/3204810
 */

use Drupal\Core\Database\Database;
use Drupal\Core\Serialization\Yaml;

$connection = Database::getConnection();

$role = Yaml::decode(file_get_contents(__DIR__ . '/drupal-10.permission-3204810.yml'));
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
