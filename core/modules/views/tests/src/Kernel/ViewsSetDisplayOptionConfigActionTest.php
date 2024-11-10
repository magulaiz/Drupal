<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel;

use Drupal\Core\Recipe\RecipeRunner;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\ViewsSetDisplayOption
 *
 * @group Recipe
 * @group views
 */
class ViewsSetDisplayOptionConfigActionTest extends ViewsKernelTestBase {

  use RecipeTestTrait;

  public static $testViews = ['entity_test_fields'];

  /**
   * Tests removing field from a default display.
   */
  public function testSetDefaultDisplay() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $pager = $view->displayHandlers->get('default')->getOption('pager');
    // Check that pager type is full.
    $this->assertSame('full', $pager['type']);
    // Apply config action that set pager to mini for default display.
    $this->applyAction('views.view.entity_test_fields');
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $pager = $view->displayHandlers->get('default')->getOption('pager');
    // Check that pager type is mini.
    $this->assertSame('mini', $pager['type']);
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
name: Set mini pager
config:
  actions:
    $config_name:
      setDisplayOption:
        option: pager
        settings:
          type: mini
          options:
            items_per_page: 5
YAML;
    $recipe = $this->createRecipe($contents);
    RecipeRunner::processRecipe($recipe);
  }

}
