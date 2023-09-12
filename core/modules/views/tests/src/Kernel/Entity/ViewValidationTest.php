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
   * @param string[] $parents
   *   The array parents of the property of the view's default display which
   *   will be set to `non_existent`.
   * @param string|null $handler_type
   *   If the property being changed refers to a handler, the type of handler
   *   that it is. Since Views handler plugins support fallbacks, this is used
   *   to set up a mocked plugin manager which returns a non-existent plugin ID
   *   as the fallback plugin ID, thus raising the expected validation error.
   *
   * @todo Remove the the $handler_type parameter and mocking the handler
   *   manager when fallback plugin IDs are no longer allowed by Views' config
   *   schema.
   *
   * @testWith [["display_plugin"], null]
   *   [["display_options", "pager", "type"], null]
   *   [["display_options", "exposed_form", "type"], null]
   *   [["display_options", "access", "type"], null]
   *   [["display_options", "style", "type"], null]
   *   [["display_options", "row", "type"], null]
   *   [["display_options", "query", "type"], null]
   *   [["display_options", "cache", "type"], null]
   *   [["display_options", "header", "non_existent", "plugin_id"], "area"]
   *   [["display_options", "footer", "non_existent", "plugin_id"], "area"]
   *   [["display_options", "empty", "non_existent", "plugin_id"], "area"]
   *   [["display_options", "arguments", "non_existent", "plugin_id"], "argument"]
   *   [["display_options", "sorts", "non_existent", "plugin_id"], "sort"]
   *   [["display_options", "fields", "non_existent", "plugin_id"], "field"]
   *   [["display_options", "filters", "non_existent", "plugin_id"], "filter"]
   *   [["display_options", "relationships", "non_existent", "plugin_id"], "relationship"]
   */
  public function testInvalidPluginId(array $parents, ?string $handler_type): void {
    if ($handler_type) {
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
