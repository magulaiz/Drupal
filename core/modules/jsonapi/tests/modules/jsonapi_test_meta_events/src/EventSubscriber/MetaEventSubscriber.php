<?php

namespace Drupal\jsonapi_test_meta_events\EventSubscriber;

use Drupal\jsonapi\Events\CollectRelationshipMetaEvent;
use Drupal\jsonapi\Events\CollectResourceObjectMetaEvent;
use Drupal\jsonapi\Events\MetaDataEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class MetaEventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      MetaDataEvents::COLLECT_RESOURCE_OBJECT_META => 'addResourceObjectMeta',
      MetaDataEvents::COLLECT_RELATIONSHIP_META => 'addRelationshipMeta',
    ];
  }

  public function addResourceObjectMeta(CollectResourceObjectMetaEvent $event) {
    $config = \Drupal::state()->get('jsonapi_test_meta_events.object_meta', [
      'enabled_type' => FALSE,
      'enabled_id' => FALSE,
      'fields' => FALSE,
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

    // Fields expect an array of field names, the value of the fields are then
    // added to the metadata.
    foreach ($config['fields'] as $field_name) {
      $event->setMeta('resource_meta_' . $field_name, $event->getResourceObject()->getField($field_name)->value);
    }

    $event->addCacheTags(['jsonapi_test_meta_events.object_meta']);
  }

  public function addRelationshipMeta(CollectRelationshipMetaEvent $event) {
    $config = \Drupal::state()->get('jsonapi_test_meta_events.relationship_meta', [
      'enabled_type' => FALSE,
      'enabled_id' => FALSE,
      'enabled_relation' => FALSE,
      'fields' => FALSE,
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
    if ($config['enabled_relation'] === FALSE || $config['enabled_relation'] !== $event->getRelationshipField()->getName()) {
      return;
    }

    $referencedEntities = $event->getRelationshipField()->referencedEntities();
    $event->addCacheTags(['jsonapi_test_meta_events.relationship_meta']);

    // If no fields are specified we just add a list of uuids to the relations
    if ($config['fields'] === FALSE) {
      $referencedEntityIds = [];
      foreach ($referencedEntities as $entity) {
        $referencedEntityIds[] = $entity->uuid();
      }

      $event->setMetaValue('relationship_meta_' . $event->getRelationshipField()->getName(), $referencedEntityIds);
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
