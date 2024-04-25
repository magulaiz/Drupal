<?php

namespace Drupal\Tests\field_ui\Traits;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides common functionality for the Entity Display test classes.
 */
trait EntityDisplayTestTrait {

  /**
   * Adds default test fields to an entity.
   *
   * @param array $field_names
   *   The field names.
   * @param string $type
   *   The type of field to create. Defaults to 'test_field'.
   */
  protected function addDefaultTestFields(array $field_names, $type = 'test_field') {
    foreach ($field_names as $field_name) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'entity_test',
        'type' => $type,
      ]);
      $field_storage->save();
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => 'entity_test',
      ]);
      $field->save();
    }
  }

  /**
   * Tests the dependencies of field components within an entity display object.
   *
   * @param array $dependent_fields
   *   An array of field names to configure on the display, mapped to module
   *   names to be used as dependencies.
   * @param \Drupal\Core\Entity\Display\EntityDisplayInterface $display
   *   An entity display to configure and test.
   * @param string $type
   *   A formatter or widget ID to use that has dynamic dependencies.
   */
  protected function configureAndTestMultipleFieldComponentDependencies(array $dependent_fields, EntityDisplayInterface $display, $type) {
    foreach ($dependent_fields as $field_name => $dependency) {
      $display->setComponent($field_name, [
        'type' => $type,
        'weight' => 0,
        'settings' => [
          'dependent_module' => $dependency,
        ],
      ]);
    }

    $dependencies = $display->calculateDependencies()->getDependencies();
    foreach ($dependent_fields as $field) {
      $this->assertContains($field, $dependencies['module']);
    }
  }

}
