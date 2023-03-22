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
    Action::create([
      'id' => 'action_form_ajax_test',
      'label' => $this->randomMachineName(),
      'type' => 'system',
      'plugin' => 'action_form_ajax_test',
    ])->save();
    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('Test Action');
    $this->assertSession()->elementExists('css', 'optgroup[label="Test Action"] option[value="action_form_ajax_test"]');
  }

}
