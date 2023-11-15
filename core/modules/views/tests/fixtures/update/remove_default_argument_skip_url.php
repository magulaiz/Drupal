<?php

/**
 * @file
 * Test fixture.
 */

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->insert('config')
  ->fields([
    'collection' => '',
    'name' => 'views.view.remove_default_argument_skip_url',
    'data' => serialize(Yaml::decode(file_get_contents('core/modules/views/tests/fixtures/update/views.view.remove_default_argument_skip_url.yml'))),
  ])
  ->execute();
