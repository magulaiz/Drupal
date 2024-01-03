<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Field;

use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Test field items with JSON field storage.
 *
 * @see EntityQueryTest::testQueryJsonBackedField()
 *
 * @group Field
 */
class JsonBackedFieldStorageTest extends EntityKernelTestBase {

  protected EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager;

  protected function setUp(): void {
    parent::setUp();

    $this->entityDefinitionUpdateManager = $this->container->get('entity.definition_update_manager');
  }

  public function testFieldMappingStorage(): void {
    $definitions['json_data'] = BaseFieldDefinition::create('json_backed_test')
      ->setLabel(t('JSON-backed data'))
      ->setRequired(TRUE);

    $this->state->set('entity_test.additional_base_field_definitions', $definitions);

    $this->entityDefinitionUpdateManager->installFieldStorageDefinition(
      'json_data',
      'entity_test',
      'entity_test',
      $definitions['json_data']
    );

    $data_map_value = [
      'key' => 'value',
      'another' => 'second value',
      'array' => [1, 2, 3, 4],
      'associative_array' => ['one' => 1, 'five' => 5],
    ];
    $entity = EntityTest::create([
      'json_data' => $data_map_value,
    ]);
    $entity->save();
    $storage = \Drupal::entityTypeManager()->getStorage('entity_test');
    $loaded = $storage->loadUnchanged($entity->id());
    $this->assertEqualsCanonicalizing($data_map_value, $loaded->get('json_data')->first()->getValue());
  }

}
