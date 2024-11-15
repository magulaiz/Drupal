<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Plugin\ConfigAction;

use Drupal\Core\Recipe\RecipeRunner;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\ViewsRemoveItemFromDisplayOption
 *
 * @group Recipe
 * @group views
 */
class ViewsRemoveItemFromDisplayOptionConfigActionTest extends ViewsKernelTestBase {

  use RecipeTestTrait;

  public static $testViews = ['entity_test_fields'];

  /**
   * Tests removing field from a default display.
   */
  public function testRemoveFieldFromDefaultDisplay() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayHasKey('name', $fields);
    // Apply config action that removes field name from default display.
    $this->applyAction('views.view.entity_test_fields');
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type now exists.
    $this->assertArrayNotHasKey('name', $fields);
  }

  /**
   * Applies a recipe with the addItemToDisplayOption action.
   *
   * @param string $config_name
   *   The name of the config object which should run the addItemToDisplayOption
   *   action.
   */
  private function applyAction(string $config_name): void {
    $contents = <<<YAML
name: Remove name field from view
config:
  actions:
    $config_name:
      removeItemFromDisplayOption:
        option: fields
        item: name
YAML;
    $recipe = $this->createRecipe($contents);
    RecipeRunner::processRecipe($recipe);
  }

}
