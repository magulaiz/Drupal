<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Tests the Entity Query relationship API.
 *
 * @group Entity
 */
class EntityQueryRelationshipMultiplePropertyBaseFieldTest extends EntityQueryRelationshipTest {

  protected static $modules = ['field_test'];

  protected function createField($selection_handler_settings) {
    /** @var \Drupal\Core\Entity\EntityDefinitionUpdateManager $manager */
    $manager = \Drupal::service('entity.definition_update_manager');
    $field = BaseFieldDefinition::create('entity_reference_with_text')
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', $selection_handler_settings);
    $this->state->set('entity_test.additional_base_field_definitions', [$this->fieldName => $field]);
    $manager->installFieldStorageDefinition($this->fieldName, 'entity_test', 'entity_test', $field);
  }

}
