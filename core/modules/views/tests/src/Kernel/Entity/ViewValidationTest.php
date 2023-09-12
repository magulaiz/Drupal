<?php

namespace Drupal\Tests\views\Kernel\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\views\Entity\View;
use Drupal\views\Plugin\ViewsHandlerManager;

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
   *   ["display_options", "header", "non_existent", "plugin_id"]
   *   ["display_options", "footer", "non_existent", "plugin_id"]
   */
  public function testInvalidPluginId(...$parents): void {
    $handler_types = ['area'];
    foreach ($handler_types as $handler_type) {
      $this->container->set("plugin.manager.views.$handler_type", new class (
        $handler_type,
        $this->container->get('container.namespaces'),
        $this->container->get('views.views_data'),
        $this->container->get('cache.discovery'),
        $this->container->get('module_handler'),
      ) extends ViewsHandlerManager {

        /**
         * {@inheritdoc}
         */
        public function getFallbackPluginId($plugin_id, array $configuration = []) {
          return 'non_existent';
        }

      });
    }

    $display = &$this->entity->getDisplay('default');
    NestedArray::setValue($display, $parents, 'non_existent');
    $property_path = 'display.default.' . implode('.', $parents);
    $this->assertValidationErrors([
      $property_path => "The 'non_existent' plugin does not exist.",
    ]);
  }

}
