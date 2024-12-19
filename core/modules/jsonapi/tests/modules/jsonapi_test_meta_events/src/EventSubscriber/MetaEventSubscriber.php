<?php

namespace Drupal\jsonapi_test_meta_events\EventSubscriber;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\jsonapi\Events\CollectRelationshipMetaEvent;
use Drupal\jsonapi\Events\CollectResourceObjectMetaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class MetaEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CollectResourceObjectMetaEvent::class => 'addResourceObjectMeta',
      CollectRelationshipMetaEvent::class => 'addRelationshipMeta',
    ];
  }

  /**
   * @param \Drupal\jsonapi\Events\CollectResourceObjectMetaEvent $event
   *   Event to be processed.
   *
   * @return void
   */
  public function addResourceObjectMeta(CollectResourceObjectMetaEvent $event): void {
    $config = \Drupal::state()->get('jsonapi_test_meta_events.object_meta', [
      'enabled_type' => FALSE,
      'enabled_id' => FALSE,
      'fields' => FALSE,
      'user_is_superuser_context' => FALSE,
    ]);

    // Only continue if the recourse type is enabled.
    if ($config['enabled_type'] === FALSE || $config['enabled_type'] !== $event->getResourceObject()->getTypeName()) {
      return;
    }

    // Only apply on the referenced ID of the resource.
    if ($config['enabled_id'] !== FALSE && $config['enabled_id'] !== $event->getResourceObject()->getId()) {
      return;
    }

    if ($config['fields'] === FALSE) {
      return;
    }

    if ($config['user_is_superuser_context']) {
      $event->addCacheContexts(['user.is_super_user']);
      $event->setMetaValue('resource_meta_user_is_superuser', (int) \Drupal::currentUser()->id() === 1 ? 'yes' : 'no');
      $event->setMetaValue('resource_meta_user_id', \Drupal::currentUser()->id());
    }

    // Fields expect an array of field names, the value of the fields are then
    // added to the metadata.
    foreach ($config['fields'] as $field_name) {
      $event->setMetaValue('resource_meta_' . $field_name, $event->getResourceObject()->getField($field_name)->value);
    }

    $event->addCacheTags(['jsonapi_test_meta_events.object_meta']);
  }

  /**
   * @param \Drupal\jsonapi\Events\CollectRelationshipMetaEvent $event
   *   Event to be processed.
   *
   * @return void
   */
  public function addRelationshipMeta(CollectRelationshipMetaEvent $event): void {
    $config = \Drupal::state()->get('jsonapi_test_meta_events.relationship_meta', [
      'enabled_type' => FALSE,
      'enabled_id' => FALSE,
      'enabled_relation' => FALSE,
      'fields' => FALSE,
      'user_is_superuser_context' => FALSE,
    ]);

    // Only continue if the recourse type is enabled.
    if ($config['enabled_type'] === FALSE || $config['enabled_type'] !== $event->getResourceObject()->getTypeName()) {
      return;
    }

    // Only apply on the referenced ID of the resource.
    if ($config['enabled_id'] !== FALSE && $config['enabled_id'] !== $event->getResourceObject()->getId()) {
      return;
    }

    // Only continue if this is the correct relation.
    if ($config['enabled_relation'] === FALSE || $config['enabled_relation'] !== $event->getRelationshipFieldName()) {
      return;
    }

    $relationshipFieldName = $event->getRelationshipFieldName();

    $field = $event->getResourceObject()->getField($relationshipFieldName);
    $referencedEntities = [];
    if ($field instanceof EntityReferenceFieldItemListInterface) {
      $referencedEntities = $field->referencedEntities();
      $event->addCacheTags(['jsonapi_test_meta_events.relationship_meta']);
    }

    if ($config['user_is_superuser_context'] ?? FALSE) {
      $event->addCacheContexts(['user.is_super_user']);
      $event->setMetaValue('resource_meta_user_is_superuser', (int) \Drupal::currentUser()->id() === 1 ? 'yes' : 'no');
    }

    // If no fields are specified we just add a list of uuids to the relations
    if ($config['fields'] === FALSE) {
      $referencedEntityIds = [];
      foreach ($referencedEntities as $entity) {
        $referencedEntityIds[] = $entity->uuid();
      }

      $event->setMetaValue('relationship_meta_' . $event->getRelationshipFieldName(), $referencedEntityIds);
      return;
    }

    // Fields expect an array of field names, the value of the fields are then
    // added to the metadata.
    foreach ($config['fields'] as $field_name) {
      $fieldValues = [];
      foreach ($referencedEntities as $entity) {
        $fieldValues[] = $entity->get($field_name)->value;
      }
      $event->setMetaValue('relationship_meta_' . $field_name, $fieldValues);
    }

  }

}
