<?php

namespace Drupal\Tests\views\Kernel\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\views\Entity\View;

/**
 * Tests validation of view entities.
 *
 * @group views
 */
class ViewValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views', 'views_test_config'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = View::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that a various plugin IDs making up a view display are validated.
   *
   * @testWith ["display_plugin"]
   *   ["display_options", "pager", "type"]
   *   ["display_options", "exposed_form", "type"]
   *   ["display_options", "access", "type"]
   *   ["display_options", "style", "type"]
   *   ["display_options", "row", "type"]
   *   ["display_options", "query", "type"]
   *   ["display_options", "cache", "type"]
   */
  public function testInvalidPluginId(...$parents): void {
    $display = &$this->entity->getDisplay('default');
    NestedArray::setValue($display, $parents, 'non_existent');
    $property_path = 'display.default.' . implode('.', $parents);
    $this->assertValidationErrors([
      $property_path => "The 'non_existent' plugin does not exist.",
    ]);
  }

}
