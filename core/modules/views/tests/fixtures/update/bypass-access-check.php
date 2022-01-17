<?php

/**
 * @file
 * Text fixture.
 */

use Drupal\Core\Database\Database;
use Drupal\Core\Serialization\Yaml;

$connection = Database::getConnection();

$connection->insert('config')
  ->fields([
    'collection' => '',
    'name' => 'views.view.test_link_bypass_access_check',
    'data' => serialize(Yaml::decode(file_get_contents(__DIR__ . '/views.view.test_link_bypass_access_check.yml'))),
  ])
  ->execute();
