<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Plugin\ConfigAction;

use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\RemoveItemFromDisplayOption
 *
 * @group Recipe
 * @group views
 */
class ViewsRemoveItemFromDisplayOptionConfigActionTest extends ViewsKernelTestBase {

  public static $testViews = ['entity_test_fields'];

  /**
   * Tests removing field from a default display.
   */
  public function testRemoveFieldFromDefaultDisplay() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field name is part of default display.
    $this->assertArrayHasKey('name', $fields);
    // Apply config action that removes field name from default display.
    $this->applyAction('views.view.entity_test_fields');
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field name does not exist now.
    $this->assertArrayNotHasKey('name', $fields);
  }

  /**
   * Applies a recipe with the removeItemFromDisplayOption action.
   *
   * @param string $config_name
   *   The name of the config object which should run the
   *   removeItemFromDisplayOption action.
   */
  private function applyAction(string $config_name): void {
    $config_action_settings = [
      'option' => 'fields',
      'item' => 'name',
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('removeItemFromDisplayOption', $config_name, $config_action_settings);
  }

}
