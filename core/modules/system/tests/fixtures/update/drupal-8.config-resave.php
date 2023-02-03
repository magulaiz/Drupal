<?php

/**
 * @file
 * Creates configuration that will be affected by re-saving.
 *
 * @see \Drupal\system\Tests\Update\ResaveConfigurationTest
 */

use Drupal\Core\Database\Database;
use Drupal\Component\Serialization\Yaml;

$connection = Database::getConnection();

$configs['config_test.dynamic.update_test'] = Yaml::decode(file_get_contents(__DIR__ . '/config_test.dynamic.update_test.yml'));

foreach ($configs as $id => $config) {
  $connection->insert('config')
    ->fields([
      'collection',
      'name',
      'data',
    ])
    ->values([
      'collection' => '',
      'name' => $id,
      'data' => serialize($config),
    ])
    ->execute();
}
