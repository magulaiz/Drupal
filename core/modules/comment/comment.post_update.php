<?php

/**
 * @file
 * Post update functions for the comment module.
 */

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Implements hook_removed_post_updates().
 */
function comment_removed_post_updates(): array {
  return [
    'comment_post_update_enable_comment_admin_view' => '9.0.0',
    'comment_post_update_add_ip_address_setting' => '9.0.0',
  ];
}

/**
 * Update comments to be revisionable.
 */
function comment_post_update_make_comment_revisionable(&$sandbox): void {
  $definition_update_manager = \Drupal::entityDefinitionUpdateManager();
  /** @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $last_installed_schema_repository */
  $last_installed_schema_repository = \Drupal::service('entity.last_installed_schema.repository');

  $entity_type = $definition_update_manager->getEntityType('comment');
  $field_storage_definitions = $last_installed_schema_repository->getLastInstalledFieldStorageDefinitions('comment');

  // Update the entity type definition.
  $entity_keys = $entity_type->getKeys();
  $entity_keys['revision'] = 'revision_id';
  $entity_keys['revision_translation_affected'] = 'revision_translation_affected';
  $entity_type->set('entity_keys', $entity_keys);
  $entity_type->set('revision_table', 'comment_revision');
  $entity_type->set('revision_data_table', 'comment_field_revision');
  $revision_metadata_keys = [
    'revision_default' => 'revision_default',
    'revision_user' => 'revision_user',
    'revision_created' => 'revision_created',
    'revision_log_message' => 'revision_log_message',
  ];
  $entity_type->set('revision_metadata_keys', $revision_metadata_keys);

  // Update the field storage definitions and add the new ones required by a
  // revisionable entity type.
  $field_storage_definitions['subject']->setRevisionable(TRUE);
  $field_storage_definitions['name']->setRevisionable(TRUE);
  $field_storage_definitions['mail']->setRevisionable(TRUE);
  $field_storage_definitions['homepage']->setRevisionable(TRUE);
  $field_storage_definitions['hostname']->setRevisionable(TRUE);
  $field_storage_definitions['created']->setRevisionable(TRUE);
  $field_storage_definitions['changed']->setRevisionable(TRUE);

  $field_storage_definitions['revision_id'] = BaseFieldDefinition::create('integer')
    ->setName('revision_id')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Revision ID'))
    ->setReadOnly(TRUE)
    ->setSetting('unsigned', TRUE);

  $field_storage_definitions['revision_default'] = BaseFieldDefinition::create('boolean')
    ->setName('revision_default')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Default revision'))
    ->setDescription(new TranslatableMarkup('A flag indicating whether this was a default revision when it was saved.'))
    ->setStorageRequired(TRUE)
    ->setInternal(TRUE)
    ->setTranslatable(FALSE)
    ->setRevisionable(TRUE);

  $field_storage_definitions['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
    ->setName('revision_translation_affected')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Revision translation affected'))
    ->setDescription(new TranslatableMarkup('Indicates if the last edit of a translation belongs to current revision.'))
    ->setReadOnly(TRUE)
    ->setRevisionable(TRUE)
    ->setTranslatable(TRUE);

  $field_storage_definitions['revision_created'] = BaseFieldDefinition::create('created')
    ->setName('revision_created')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Revision create time'))
    ->setDescription(new TranslatableMarkup('The time that the current revision was created.'))
    ->setRevisionable(TRUE);

  $field_storage_definitions['revision_user'] = BaseFieldDefinition::create('entity_reference')
    ->setName('revision_user')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Revision user'))
    ->setDescription(new TranslatableMarkup('The user ID of the author of the current revision.'))
    ->setSetting('target_type', 'user')
    ->setRevisionable(TRUE);

  $field_storage_definitions['revision_log_message'] = BaseFieldDefinition::create('string_long')
    ->setName('revision_log_message')
    ->setTargetEntityTypeId('comment')
    ->setTargetBundle(NULL)
    ->setLabel(new TranslatableMarkup('Revision log message'))
    ->setDescription(new TranslatableMarkup('Briefly describe the changes you have made.'))
    ->setRevisionable(TRUE)
    ->setDefaultValue('');

  $definition_update_manager->updateFieldableEntityType($entity_type, $field_storage_definitions, $sandbox);
}

/**
 * Set initial values for new revision fields.
 */
function comment_post_update_set_initial_revision_field_values(&$sandbox): void {
  $connection = \Drupal::database();
  $fields = [
    'revision_created' => 'created',
    'revision_user' => 'uid',
  ];

  $definition = \Drupal::entityTypeManager()->getDefinition('comment');
  $dataTable = $definition->getDataTable();
  $revisionTable = $definition->getRevisionTable();

  foreach ($fields as $newFieldName => $existingFieldName) {
    $subQuery = $connection->select($dataTable, 'cfd')
      ->fields('cfd', [$existingFieldName])
      ->where("$revisionTable.cid = cfd.cid AND $revisionTable.revision_id = cfd.revision_id AND $revisionTable.langcode = cfd.langcode");

    $connection->update($revisionTable)
      ->expression($newFieldName, $subQuery)
      ->isNull($newFieldName)
      ->execute();
  }
}
