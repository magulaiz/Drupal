<?php

namespace Drupal\KernelTests\Core\Field;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\entity_test_update\Entity\EntityTestUpdate;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests map base fields.
 *
 * @group Field
 */
class MapBaseFieldTest extends EntityKernelTestBase {

  /**
   * The entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  protected $entityDefinitionUpdateManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test_update'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityDefinitionUpdateManager = $this->container->get('entity.definition_update_manager');

    // Install every entity type's schema that wasn't installed in the parent
    // method.
    foreach (array_diff_key($this->entityTypeManager->getDefinitions(), array_flip(['user', 'entity_test'])) as $entity_type_id => $entity_type) {
      $this->installEntitySchema($entity_type_id);
    }
  }

  /**
   * Data provider for testMapItemBaseField().
   */
  public function provideMapItemBaseFieldData() {
    return [
      'single item cardinality, stored in base table' => [1],
      'multiple item cardinality, stored in dedicated table' => [FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED],
    ];
  }

  /**
   * Tests uninstalling map item base field.
   *
   * @dataProvider provideMapItemBaseFieldData
   */
  public function testMapItemBaseField(int $cardinality) {
    $definitions['data_map'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data'))
      ->setCardinality($cardinality)
      ->setRequired(TRUE);

    $this->state->set('entity_test_update.additional_base_field_definitions', $definitions);

    $this->entityDefinitionUpdateManager->installFieldStorageDefinition('data_map', 'entity_test_update', 'entity_test', $definitions['data_map']);

    $dataMapValue = [
      'key' => 'value',
      'another' => ['array', 'indexed' => 'value'],
    ];

    $entity = EntityTestUpdate::create([
      'data_map' => $dataMapValue,
    ]);
    $entity->save();
    $entityId = $entity->id();

    $storage = \Drupal::entityTypeManager()->getStorage('entity_test_update');
    $entity = $storage->loadUnchanged($entityId);
    $this->assertSame($dataMapValue, $entity->get('data_map')->first()->getValue());

    $this->entityDefinitionUpdateManager->uninstallFieldStorageDefinition($definitions['data_map']);
  }

}
