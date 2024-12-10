<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Entity\EntityTypeEvents;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Field\FieldStorageDefinitionEvents;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_test\FieldStorageDefinition;
use Drupal\Tests\system\Functional\Entity\Traits\EntityDefinitionTestTrait;

/**
 * Tests EntityDefinitionUpdateManager functionality.
 *
 * @coversDefaultClass \Drupal\Core\Entity\EntityDefinitionUpdateManager
 *
 * @group Entity
 * @group #slow
 */
class EntityDefinitionUpdateTest extends EntityKernelTestBase {

  use EntityDefinitionTestTrait;

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test_update', 'language'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->entityDefinitionUpdateManager = $this->container->get('entity.definition_update_manager');
    $this->entityFieldManager = $this->container->get('entity_field.manager');
    $this->database = $this->container->get('database');
  }

  /**
   * Tests that new entity type definitions are correctly handled.
   */
  public function testNewEntityType(): void {
    $entity_type_id = 'entity_test_new';
    $schema = $this->database->schema();

    // Check that the "entity_test_new" is not defined.
    $entity_types = $this->entityTypeManager->getDefinitions();
    $this->assertFalse(isset($entity_types[$entity_type_id]), 'The "entity_test_new" entity type does not exist.');
    $this->assertFalse($schema->tableExists($entity_type_id), 'Schema for the "entity_test_new" entity type does not exist.');

    // Check that the "entity_test_new" is now defined and the related schema
    // has been created.
    $this->enableNewEntityType();
    $entity_types = $this->entityTypeManager->getDefinitions();
    $this->assertTrue(isset($entity_types[$entity_type_id]), 'The "entity_test_new" entity type exists.');
    $this->assertTrue($schema->tableExists($entity_type_id), 'Schema for the "entity_test_new" entity type has been created.');
  }

  /**
   * Tests installing an additional base field while installing an entity type.
   *
   * @covers ::installFieldableEntityType
   */
  public function testInstallAdditionalBaseFieldDuringFieldableEntityTypeInstallation(): void {
    $entity_type = clone $this->entityTypeManager->getDefinition('entity_test_update');
    $field_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('entity_test_update');

    // Enable the creation of a new base field during the installation of a
    // fieldable entity type.
    $this->state->set('entity_test_update.install_new_base_field_during_create', TRUE);

    // Install the entity type and check that the additional base field was also
    // installed.
    $this->entityDefinitionUpdateManager->installFieldableEntityType($entity_type, $field_storage_definitions);

    // Check whether the 'new_base_field' field has been installed correctly.
    $field_storage_definition = $this->entityDefinitionUpdateManager->getFieldStorageDefinition('new_base_field', 'entity_test_update');
    $this->assertNotNull($field_storage_definition);
  }

  /**
   * Tests entity type and field storage definition events.
   */
  public function testDefinitionEvents(): void {
    /** @var \Drupal\entity_test\EntityTestDefinitionSubscriber $event_subscriber */
    $event_subscriber = $this->container->get('entity_test.definition.subscriber');
    $event_subscriber->enableEventTracking();
    $event_subscriber->enableLiveDefinitionUpdates();

    // Test field storage definition events.
    $storage_definition = FieldStorageDefinition::create('string')
      ->setName('field_storage_test')
      ->setLabel(new TranslatableMarkup('Field storage test'))
      ->setTargetEntityTypeId('entity_test_rev');

    $this->assertFalse($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::CREATE), 'Entity type create was not dispatched yet.');
    \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionCreate($storage_definition);
    $this->assertTrue($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::CREATE), 'Entity type create event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(FieldStorageDefinitionEvents::CREATE), 'Last installed field storage definition was created before the event was fired.');

    // Check that the newly added field can be retrieved from the live field
    // storage definitions.
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('entity_test_rev');
    $this->assertArrayHasKey('field_storage_test', $field_storage_definitions);

    $updated_storage_definition = clone $storage_definition;
    $updated_storage_definition->setLabel(new TranslatableMarkup('Updated field storage test'));
    $this->assertFalse($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::UPDATE), 'Entity type update was not dispatched yet.');
    \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionUpdate($updated_storage_definition, $storage_definition);
    $this->assertTrue($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::UPDATE), 'Entity type update event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(FieldStorageDefinitionEvents::UPDATE), 'Last installed field storage definition was updated before the event was fired.');

    // Check that the updated field can be retrieved from the live field storage
    // definitions.
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('entity_test_rev');
    $this->assertEquals(new TranslatableMarkup('Updated field storage test'), $field_storage_definitions['field_storage_test']->getLabel());

    $this->assertFalse($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::DELETE), 'Entity type delete was not dispatched yet.');
    \Drupal::service('field_storage_definition.listener')->onFieldStorageDefinitionDelete($storage_definition);
    $this->assertTrue($event_subscriber->hasEventFired(FieldStorageDefinitionEvents::DELETE), 'Entity type delete event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(FieldStorageDefinitionEvents::DELETE), 'Last installed field storage definition was deleted before the event was fired.');

    // Check that the deleted field can no longer be retrieved from the live
    // field storage definitions.
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('entity_test_rev');
    $this->assertArrayNotHasKey('field_storage_test', $field_storage_definitions);

    // Test entity type events.
    $entity_type = $this->entityTypeManager->getDefinition('entity_test_rev');

    $this->assertFalse($event_subscriber->hasEventFired(EntityTypeEvents::CREATE), 'Entity type create was not dispatched yet.');
    \Drupal::service('entity_type.listener')->onEntityTypeCreate($entity_type);
    $this->assertTrue($event_subscriber->hasEventFired(EntityTypeEvents::CREATE), 'Entity type create event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(EntityTypeEvents::CREATE), 'Last installed entity type definition was created before the event was fired.');

    $updated_entity_type = clone $entity_type;
    $updated_entity_type->set('label', new TranslatableMarkup('Updated entity test rev'));
    $this->assertFalse($event_subscriber->hasEventFired(EntityTypeEvents::UPDATE), 'Entity type update was not dispatched yet.');
    \Drupal::service('entity_type.listener')->onEntityTypeUpdate($updated_entity_type, $entity_type);
    $this->assertTrue($event_subscriber->hasEventFired(EntityTypeEvents::UPDATE), 'Entity type update event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(EntityTypeEvents::UPDATE), 'Last installed entity type definition was updated before the event was fired.');

    // Check that the updated definition can be retrieved from the live entity
    // type definitions.
    $entity_type = $this->entityTypeManager->getDefinition('entity_test_rev');
    $this->assertEquals(new TranslatableMarkup('Updated entity test rev'), $entity_type->getLabel());

    $this->assertFalse($event_subscriber->hasEventFired(EntityTypeEvents::DELETE), 'Entity type delete was not dispatched yet.');
    \Drupal::service('entity_type.listener')->onEntityTypeDelete($entity_type);
    $this->assertTrue($event_subscriber->hasEventFired(EntityTypeEvents::DELETE), 'Entity type delete event successfully dispatched.');
    $this->assertTrue($event_subscriber->hasDefinitionBeenUpdated(EntityTypeEvents::DELETE), 'Last installed entity type definition was deleted before the event was fired.');

    // Check that the deleted entity type can no longer be retrieved from the
    // live entity type definitions.
    $this->assertNull($this->entityTypeManager->getDefinition('entity_test_rev', FALSE));
  }

  /**
   * Tests the error handling when using initial values from another field.
   */
  public function testInitialValueFromFieldErrorHandling(): void {
    // Check that setting invalid values for 'initial value from field' doesn't
    // work.
    try {
      $this->addBaseField();
      $storage_definition = BaseFieldDefinition::create('string')
        ->setLabel(t('A new base field'))
        ->setInitialValueFromField('field_that_does_not_exist');
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition('new_base_field', 'entity_test_update', 'entity_test', $storage_definition);
      $this->fail('Using a non-existent field as initial value does not work.');
    }
    catch (FieldException $e) {
      $this->assertEquals('Illegal initial value definition on new_base_field: The field field_that_does_not_exist does not exist.', $e->getMessage());
    }

    try {
      $this->addBaseField();
      $storage_definition = BaseFieldDefinition::create('integer')
        ->setLabel(t('A new base field'))
        ->setInitialValueFromField('name');
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition('new_base_field', 'entity_test_update', 'entity_test', $storage_definition);
      $this->fail('Using a field of a different type as initial value does not work.');
    }
    catch (FieldException $e) {
      $this->assertEquals('Illegal initial value definition on new_base_field: The field types do not match.', $e->getMessage());
    }

    try {
      // Add a base field that will not be stored in the shared tables.
      $initial_field = BaseFieldDefinition::create('string')
        ->setName('initial_field')
        ->setLabel(t('An initial field'))
        ->setCardinality(2);
      $this->state->set('entity_test_update.additional_base_field_definitions', ['initial_field' => $initial_field]);
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition('initial_field', 'entity_test_update', 'entity_test', $initial_field);

      // Now add the base field which will try to use the previously added field
      // as the source of its initial values.
      $new_base_field = BaseFieldDefinition::create('string')
        ->setName('new_base_field')
        ->setLabel(t('A new base field'))
        ->setInitialValueFromField('initial_field');
      $this->state->set('entity_test_update.additional_base_field_definitions', ['initial_field' => $initial_field, 'new_base_field' => $new_base_field]);
      $this->entityDefinitionUpdateManager->installFieldStorageDefinition('new_base_field', 'entity_test_update', 'entity_test', $new_base_field);
      $this->fail('Using a field that is not stored in the shared tables as initial value does not work.');
    }
    catch (FieldException $e) {
      $this->assertEquals('Illegal initial value definition on new_base_field: Both fields have to be stored in the shared entity tables.', $e->getMessage());
    }
  }

  /**
   * @covers ::getEntityTypes
   */
  public function testGetEntityTypes(): void {
    $entity_type_definitions = $this->entityDefinitionUpdateManager->getEntityTypes();

    // Ensure that we have at least one entity type to check below.
    $this->assertGreaterThanOrEqual(1, count($entity_type_definitions));

    foreach ($entity_type_definitions as $entity_type_id => $entity_type) {
      $this->assertEquals($this->entityDefinitionUpdateManager->getEntityType($entity_type_id), $entity_type);
    }
  }

}
