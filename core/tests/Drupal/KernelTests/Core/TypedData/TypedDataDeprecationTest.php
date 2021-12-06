<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests deprecation of a Data Type plugin.
 *
 * @group TypedData
 * @group legacy
 */
class TypedDataDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['typed_data_deprecation_test'];

  /**
   * Test plugin deprecation.
   */
  public function testTypeDataDeprecation() {
    $this->expectDeprecation('The test_deprecated_data_type plugin is deprecated');

    // Create a data definition for the data type plugin.
    $definition = \Drupal::typedDataManager()->createDataDefinition('test_deprecated_data_type');
    \Drupal::typedDataManager()->create($definition);
  }

}
