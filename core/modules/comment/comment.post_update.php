<?php

/**
 * @file
 * Post update functions for the comment module.
 */

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchemaConverter;

/**
 * Implements hook_removed_post_updates().
 */
function comment_removed_post_updates() {
  return [
    'comment_post_update_enable_comment_admin_view' => '9.0.0',
    'comment_post_update_add_ip_address_setting' => '9.0.0',
  ];
}

/**
 * Update comments to be revisionable.
 */
function comment_post_update_make_comment_revisionable(&$sandbox) {
  $schema_converter = new SqlContentEntityStorageSchemaConverter(
    'comment',
    \Drupal::entityTypeManager(),
    \Drupal::entityDefinitionUpdateManager(),
    \Drupal::service('entity.last_installed_schema.repository'),
    \Drupal::keyValue('entity.storage_schema.sql'),
    \Drupal::database()
  );
  $schema_converter->convertToRevisionable(
    $sandbox,
    [
      'subject',
      'name',
      'mail',
      'homepage',
      'hostname',
      'created',
      'changed',
    ]
  );
}
