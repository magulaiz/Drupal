<?php

/**
 * @file
 * Adds image media type config for view mode update test.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

// Add an image media type.
$media_type = [];
$media_type['langcode'] = 'en';
$media_type['status'] = TRUE;
$media_type['dependencies'] = [];
$media_type['id'] = 'image';
$media_type['uuid'] = 'ec08b686-5d58-432a-b5ba-f82164c1c592';
$media_type['label'] = 'Image';
$media_type['description'] = 'Use local images for reusable media.';
$media_type['source'] = 'image';
$media_type['queue_thumbnail_downloads'] = FALSE;
$media_type['new_revision'] = TRUE;
$media_type['source_configuration'] = [
  'source_field' => 'field_media_image',
];
$media_type['field_map'] = [];
$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'media.type.image',
    'data' => serialize($media_type),
  ])
  ->execute();
