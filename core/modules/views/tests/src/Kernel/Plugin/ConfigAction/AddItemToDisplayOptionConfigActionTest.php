<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Plugin\ConfigAction;

use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\AddItemToDisplayOption
 *
 * @group Recipe
 * @group views
 */
class AddItemToDisplayOptionConfigActionTest extends ViewsKernelTestBase {

  public static $testViews = ['entity_test_fields'];

  /**
   * Tests adding a field to a default display.
   */
  public function testAddFieldToDefaultDisplay() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayNotHasKey('type', $fields);
    // Apply config action that adds field type to default display.
    $this->applyAction('views.view.entity_test_fields');
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type now exists.
    $this->assertArrayHasKey('type', $fields);
    // Try to apply the action again without allow_update flag.
    $this->expectException(ConfigActionException::class);
    $this->expectExceptionMessage('Item type already exists in default display for fields');
    $this->applyAction('views.view.entity_test_fields');
  }

  /**
   * Applies a recipe with the addItemToDisplayOption action.
   *
   * @param string $config_name
   *   The name of the config object which should run the addItemToDisplayOption
   *   action.
   * @param bool $allow_update
   *   Whether update is allowed or not.
   */
  private function applyAction(string $config_name, bool $allow_update = FALSE): void {
    $config_action_settings = [
      'allow_update' => $allow_update,
      'option' => 'fields',
      'item' => 'type',
      'settings' => [
        'id' => 'type',
        'table' => 'entity_test',
        'field' => 'type',
        'entity_type' => 'entity_test',
        'entity_field' => 'type',
        'plugin_id' => 'field',
        'exclude' => FALSE,
        'alter' => [
          'alter_text' => FALSE,
        ],
        'element_class' => '',
        'empty' => '',
        'hide_empty' => FALSE,
        'empty_zero' => FALSE,
        'hide_alter_empty' => TRUE,
      ],
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('addItemToDisplayOption', $config_name, $config_action_settings);
  }

}
