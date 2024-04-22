<?php

namespace Drupal\Tests\action\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test behaviors when visiting the action listing page.
 *
 * @group action
 */
class ActionListTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['action', 'user', 'action_form_ajax_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the behavior when there are no actions to list in the admin page.
   */
  public function testEmptyActionList() {
    // Create a user with permission to view the actions administration pages.
    $this->drupalLogin($this->drupalCreateUser(['administer actions']));

    // Ensure the empty text appears on the action list page.
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('action');
    $actions = $storage->loadMultiple();
    $storage->delete($actions);
    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('There are no actions yet.');
  }

  /**
   * Tests that non-configurable actions can be created by the UI.
   */
  public function testNonConfigurableActionsCanBeCreated() {
    $this->drupalLogin($this->drupalCreateUser(['administer actions']));
    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->elementExists('css', 'select > option[value="user_block_user_action"]');
  }

  /**
   * Tests the category behaviour on the Actions page.
   */
  public function testCategoryInActionList() {
    // Create a user with permission to view the actions administration pages.
    $this->drupalLogin($this->drupalCreateUser(['administer actions']));

    // Make a POST request to admin/config/system/actions.
    $edit = [];
    $edit['id'] = 'action_form_ajax_test';
    $edit['label'] = $this->randomMachineName();
    $edit['type'] = 'system';
    $edit['plugin'] = 'action_form_ajax_test';
    $this->drupalGet('admin/config/system/actions');
    $this->submitForm($edit, 'Create');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('Test Action');
    $this->assertSession()->elementExists('css', 'optgroup[label="Test Action"] option[value="action_form_ajax_test"]');
  }

}
