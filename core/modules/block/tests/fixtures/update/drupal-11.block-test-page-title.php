<?php

/**
 * @file
 * Partial database to mimic the installation of the block_test module.
 */

use Drupal\Core\Database\Database;
use Symfony\Component\Yaml\Yaml;

$connection = Database::getConnection();

$key_value = $connection->select('key_value')->fields('key_value', [])->execute();

// Update the schema version.
$connection->update('key_value')
  ->fields([
    'value' => 'i:11100;',
  ])
  ->condition('name', 'block')
  ->execute();

// Update core.extension.
$extensions = $connection->select('config')
  ->fields('config', ['data'])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
$extensions['module']['block'] = 11100;
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
  ])
  ->condition('collection', '')
  ->condition('name', 'core.extension')
  ->execute();

// Update the block configuration for stark and claro.
$config = file_get_contents(__DIR__ . '/block.block.stark_page_title.yml');
$config = Yaml::parse($config);
$connection->update('config')
  ->fields([
    'data' => serialize($config),
  ])
  ->condition('name', 'block.block.' . $config['id'])
  ->execute();

$config = file_get_contents(__DIR__ . '/block.block.claro_page_title.yml');
$config = Yaml::parse($config);
$connection->update('config')
  ->fields([
    'data' => serialize($config),
  ])
  ->condition('name', 'block.block.' . $config['id'])
  ->execute();
