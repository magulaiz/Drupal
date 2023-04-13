<?php

namespace Drupal\Tests\field_ui\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * AJAX modal tests for field config list builder.
 *
 * @group field_ui
 */
class FieldConfigListBuilderTest extends WebDriverTestBase {

  /**
   * A test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'test']);

    $this->adminUser = $this->drupalCreateUser([
      'administer content types',
      'administer node fields',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests that field type column storage forms use a modal dialog.
   */
  public function testFieldTypeColumnStorageForm() {
    $this->drupalGet('admin/structure/types/manage/test/fields');
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->click('table tr:contains(body) a:contains("Text (formatted, long, with summary)")');
    $assert_session->assertWaitOnAjaxRequest();
    $modal = $assert_session->waitForElementVisible('css', '#drupal-modal');
    $this->assertTrue($modal->isVisible(), 'Modal window found.');

    // Save the field settings.
    $save_button = $assert_session->waitForElementVisible('css', '.ui-dialog button:contains(Save field settings)');
    $this->assertTrue($save_button->isVisible(), 'Save field settings button found.');
    $save_button->click();
    $assert_session->waitForElementVisible('css', '.messages');
    $this->assertSession()->pageTextContains('Updated field Body field settings.');
  }

  /**
   * Test that field configuration forms use a modal dialog.
   */
  public function testFieldConfigurationForm() {
    $this->drupalGet('admin/structure/types/manage/test/fields');
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->click('table tr:contains(body) a:contains(Edit)');
    $assert_session->assertWaitOnAjaxRequest();
    $modal = $assert_session->waitForElementVisible('css', '#drupal-modal');
    $this->assertTrue($modal->isVisible(), 'Modal window found.');

    // Save the field settings.
    $save_button = $assert_session->waitForElementVisible('css', '.ui-dialog button:contains(Save settings)');
    $this->assertTrue($save_button->isVisible(), 'Save settings button found.');
    $save_button->click();
    $assert_session->waitForElementVisible('css', '.messages');
    $this->assertSession()->pageTextContains('Saved Body configuration.');
  }

  /**
   * Test that field storage forms use a modal dialog.
   */
  public function testFieldStorageForm() {
    $this->drupalGet('admin/structure/types/manage/test/fields');
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Expand the operations for the field and click the storage button.
    $this->click('table tr:contains(body) button .dropbutton-arrow');
    $storage_button = $assert_session->waitForElementVisible('css', 'table tr:contains(body) a:contains(Storage settings)');
    $this->assertTrue($storage_button->isVisible(), 'Storage settings button found.');
    $storage_button->click();
    $assert_session->assertWaitOnAjaxRequest();

    $modal = $assert_session->waitForElementVisible('css', '#drupal-modal');
    $this->assertTrue($modal->isVisible(), 'Modal window found.');

    // Close the modal and check that the focus goes to the drop button.
    $close_button = $assert_session->waitForElementVisible('css', '.ui-dialog button:contains(Close)');
    $close_button->click();
    $assert_session->assertNoElementAfterWait('css', '#drupal-modal');
    $assert_session->assertNoElementAfterWait('css', '.dropbutton-wrapper.open');
    $this->assertJsCondition('document.activeElement === document.querySelector("li.dropbutton-toggle > button")');

    // Open modal again.
    $this->click('table tr:contains(body) button .dropbutton-arrow');
    $storage_button = $assert_session->waitForElementVisible('css', 'table tr:contains(body) a:contains(Storage settings)');
    $this->assertTrue($storage_button->isVisible(), 'Storage settings button found.');
    $storage_button->click();
    $assert_session->assertWaitOnAjaxRequest();

    // Save the field storage settings.
    $save_button = $assert_session->waitForElementVisible('css', '.ui-dialog button:contains(Save settings)');
    $this->assertTrue($save_button->isVisible(), 'Save settings button found.');
    $save_button->click();
    $assert_session->waitForElementVisible('css', '.messages');
    $this->assertSession()->pageTextContains('Updated field Body field settings.');
  }

  /**
   * Test that delete confirm forms use a modal dialog.
   */
  public function testDeleteConfirmForm() {
    $this->drupalGet('admin/structure/types/manage/test/fields');
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Expand the operations for the field and click the delete button.
    $this->click('table tr:contains(body) button .dropbutton-arrow');
    $delete_button = $assert_session->waitForElementVisible('css', 'table tr:contains(body) a:contains(Delete)');
    $this->assertTrue($delete_button->isVisible(), 'Delete field button found.');
    $delete_button->click();
    $assert_session->assertWaitOnAjaxRequest();

    $modal = $assert_session->waitForElementVisible('css', '#drupal-modal');
    $this->assertTrue($modal->isVisible(), 'Modal window found.');

    // Delete the field and check that it was deleted.
    $delete_button = $assert_session->waitForElementVisible('css', '.ui-dialog button:contains(Delete)');
    $this->assertTrue($delete_button->isVisible(), 'Delete button found.');
    $delete_button->click();
    $this->assertNull(FieldConfig::loadByName('node', 'test', 'body'), 'Field was deleted.');
  }

}
