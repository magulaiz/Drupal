<?php

namespace Drupal\KernelTests\Core\Entity;

/**
 * Tests the Entity Query relationship API.
 *
 * @group Entity
 */
class EntityQueryRelationshipMultiplePropertyFieldTest extends EntityQueryRelationshipTest {

  protected static $modules = ['field_test'];

  protected function createField($selection_handler_settings) {
    $this->createEntityReferenceField('entity_test', 'test_bundle', $this->fieldName, NULL, 'taxonomy_term', 'default', $selection_handler_settings, 1, 'entity_reference_with_text');
  }

}
