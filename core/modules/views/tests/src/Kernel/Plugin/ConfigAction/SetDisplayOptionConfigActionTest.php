<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Plugin\ConfigAction;

use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * @covers \Drupal\views\Plugin\ConfigAction\SetDisplayOption
 *
 * @group Recipe
 * @group views
 */
class SetDisplayOptionConfigActionTest extends ViewsKernelTestBase {

  public static $testViews = [
    'entity_test_fields',
    'test_disabled_display',
  ];

  /**
   * Tests changing of view pager.
   */
  public function testSetViewsPager() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $pager = $view->displayHandlers->get('default')->getOption('pager');
    // Check that pager type is full.
    $this->assertSame('full', $pager['type']);
    // Apply config action that set pager to mini for default display.
    $config_action_settings = [
      'option' => 'pager',
      'settings' => [
        'type' => 'mini',
        'options' => [
          'items_per_page' => 5,
        ],
      ],
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', 'views.view.entity_test_fields', $config_action_settings);
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $pager = $view->displayHandlers->get('default')->getOption('pager');
    // Check that pager type is mini.
    $this->assertSame('mini', $pager['type']);
  }

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
    $config_action_settings = [
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
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', 'views.view.entity_test_fields', $config_action_settings);
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type now exists.
    $this->assertArrayHasKey('type', $fields);
    // Try to apply the action again without allow_update flag.
    $this->expectException(ConfigActionException::class);
    $this->expectExceptionMessage('Item type already exists in default display for fields');
    $config_action_settings['allow_update'] = FALSE;
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', 'views.view.entity_test_fields', $config_action_settings);
  }

  /**
   * Tests adding multiple fields to a default display.
   */
  public function testAddMultipleFieldsToDefaultDisplay() : void {
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayNotHasKey('type', $fields);
    // Check that field user_id is not part of default display.
    $this->assertArrayNotHasKey('user_id', $fields);
    // Apply config action that adds field type to default display.
    $config_action_settings = [
      [
        'option' => 'fields',
        'item' => 'user_id',
        'settings' => [
          'id' => 'user_id',
          'table' => 'entity_test',
          'field' => 'user_id',
          'plugin_id' => 'field',
          'entity_type' => 'entity_test',
          'entity_field' => 'user_id',
        ],
      ],
      [
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
      ],
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', 'views.view.entity_test_fields', $config_action_settings);
    $view = Views::getView('entity_test_fields');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type now exists.
    $this->assertArrayHasKey('type', $fields);
    // Check that field user_id now exists.
    $this->assertArrayHasKey('user_id', $fields);
  }

  /**
   * Tests adding field to a new display with override.
   */
  public function testAddFieldToOverriddenDisplay() : void {
    $view = Views::getView('test_disabled_display');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayNotHasKey('type', $fields);
    // Create a new display.
    $config_action_settings = [
      'old_display_id' => 'page_1',
      'new_display_type' => 'page',
    ];
    $this->container->get('plugin.manager.config_action')->applyAction('test_disabled_display', 'views.view.entity_test_fields', $config_action_settings);
    $view = Views::getView('test_disabled_display');
    // Confirm that new display was created.
    $this->assertTrue($view->displayHandlers->has('page_3'));
    // Apply config action that adds field type to page_3 display only.
    $config_action_settings = [
      'display_id' => 'page_3',
      'option' => 'fields',
      'item' => 'type',
      'override' => TRUE,
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
    $this->container->get('plugin.manager.config_action')->applyAction('setDisplayOption', 'views.view.test_disabled_display', $config_action_settings);
    // Check that field was added to page_3 and not to default.
    $view = Views::getView('test_disabled_display');
    $view->setDisplay();
    $fields = $view->displayHandlers->get('default')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayNotHasKey('type', $fields);
    $view->setDisplay('page_3');
    $fields = $view->displayHandlers->get('page_3')->getOption('fields');
    // Check that field type is not part of default display.
    $this->assertArrayHasKey('type', $fields);

  }

}
