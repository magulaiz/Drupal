<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Plugin\ConfigAction;

use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\SetDisplayOption
 *
 * @group Recipe
 * @group views
 */
class SetDisplayOptionConfigActionTest extends ViewsKernelTestBase {

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
   * Applies a recipe with the setDisplayOption action.
   *
   * @param string $config_name
   *   The name of the config object which should run the setDisplayOption
   *   action.
   */
  private function applyAction(string $config_name): void {
    $config_action_settings = [
      'option' => 'pager',
      'settings' => [
        'type' => 'mini',
        'options' => [
          'items_per_page' => 5,
        ],
      ],
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', $config_name, $config_action_settings);
  }

}
