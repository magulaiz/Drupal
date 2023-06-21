<?php

namespace Drupal\Tests\options\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the Options field UI functionality.
 *
 * @group options
 */
class OptionsFieldUITest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'options',
    'field_test',
    'taxonomy',
    'field_ui',
  ];
  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Machine name of the created content type.
   *
   * @var string
   */
  protected $type;

  /**
   * Name of the option field.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * Admin path to manage field storage settings.
   *
   * @var string
   */
  protected $adminPath;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create test user.
    $admin_user = $this->drupalCreateUser([
      'access content',
      'administer taxonomy',
      'access administration pages',
      'administer site configuration',
      'administer content types',
      'administer nodes',
      'bypass node access',
      'administer node fields',
      'administer node display',
    ]);
    $this->drupalLogin($admin_user);

    $type = $this->drupalCreateContentType(['name' => 'plan', 'type' => 'plan']);
    $this->type = $type->id();
  }

  public function testOptionsAllowedValuesText() {
    $this->fieldName = 'field_options_text';
    $this->createOptionsField('list_string');
    $page = $this->getSession()->getPage();

    $this->drupalGet($this->adminPath);

    $page->fillField('settings[allowed_values][table][0][item][label]', 'First');
    $page->pressButton('Add another item');
    $this->assertSession()->waitForElementVisible('css', '[name="settings[allowed_values][table][1][item][label]"]');
    $page->fillField('settings[allowed_values][table][1][item][label]', 'Second');
    $page->pressButton('Add another item');
    $this->assertSession()->waitForElementVisible('css', '[name="settings[allowed_values][table][2][item][label]"]');
    $page->fillField('settings[allowed_values][table][2][item][label]', 'Third');
    $this->assertSession()->waitForText('Machine name: third');
    $page->pressButton('Save field settings');

    $this->drupalGet($this->adminPath);
    $this->assertOrder(['First', 'Second', 'Third', '']);
    $drag_handle = $page->find('css', '[data-drupal-selector="edit-settings-allowed-values-table-0"] .tabledrag-handle');
    $target = $page->find('css', '[data-drupal-selector="edit-settings-allowed-values-table-2"]');

    // Change the order the items appear.
    $drag_handle->dragTo($target);
    $this->assertOrder(['Second', 'Third', 'First', '']);
    $page->pressButton('Save field settings');
    $this->drupalGet($this->adminPath);

    // Confirm the change in order was saved.
    $this->assertOrder(['Second', 'Third', 'First', '']);

    // Delete an item.
    $page->pressButton('remove_row_button__1');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertOrder(['Second', 'First', '']);
    $page->pressButton('Save field settings');
    $this->drupalGet($this->adminPath);

    // Confirm the item removal was saved.
    $this->assertOrder(['Second', 'First', '']);
  }

  protected function assertOrder($expected) {
    $page = $this->getSession()->getPage();
    $inputs = $page->findAll('css', '.draggable .form-text.machine-name-source');
    foreach ($expected as $step => $expected_input_value) {
      $value = $inputs[$step]->getValue();
      $this->assertSame($expected_input_value, $value, "Item $step should be $expected_input_value, but got $value");
    }
  }

  /**
   * Helper function to create list field of a given type.
   *
   * @param string $type
   *   One of 'list_integer', 'list_float' or 'list_string'.
   */
  protected function createOptionsField($type) {
    // Create a field.
    FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'type' => $type,
    ])->save();
    FieldConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'bundle' => $this->type,
    ])->save();

    \Drupal::service('entity_display.repository')
      ->getFormDisplay('node', $this->type)
      ->setComponent($this->fieldName)
      ->save();

    $this->adminPath = 'admin/structure/types/manage/' . $this->type . '/fields/node.' . $this->type . '.' . $this->fieldName . '/storage';
  }

}
