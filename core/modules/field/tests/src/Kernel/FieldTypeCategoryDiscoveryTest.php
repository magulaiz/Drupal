<?php

namespace Drupal\Tests\field\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests discovery of field type categories provided by modules.
 *
 * @group field
 */
class FieldTypeCategoryDiscoveryTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'field_plugins_test',
  ];

  /**
   * Tests custom field type categories created by modules.
   */
  public function testFieldTypeCategories() {
    $category = \Drupal::service('plugin.manager.field_type_category_info')->createInstance('test_category');
    $expected = [
      'Test category',
      'This is a test field type category.',
      -10,
    ];

    $this->assertSame($expected, [
      $category->getLabel()->render(),
      $category->getDescription()->render(),
      $category->getWeight(),
    ]);
  }

}
