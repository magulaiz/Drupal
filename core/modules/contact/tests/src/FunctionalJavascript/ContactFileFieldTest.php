<?php

declare(strict_types=1);

namespace Drupal\Tests\contact\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests file fields when added to contact forms.
 *
 * This tests that any file fields which are added to contact forms
 * initially get their scheme set to 'private' (when available).
 *
 * @group contact
 */
class ContactFileFieldTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'file',
    'contact',
    'field',
    'field_ui',
    'contact_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests file scheme when private files are configured.
   */
  public function testFileFieldHasPrivateSchemeByDefault(): void {
    // Create and log in administrative user.
    $admin_user = $this->drupalCreateUser([
      'access site-wide contact form',
      'administer contact forms',
      'administer permissions',
      'administer users',
      'access site reports',
      'administer contact_message display',
      'administer contact_message fields',
      'administer contact_message form display',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/contact/manage/feedback/fields/add-field');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $page->find('xpath', '//input[@value="file_upload"]')->click();
    $this->assertNotEmpty($file_field = $page->find('css', '[name="new_storage_type"][value="file_upload"]')->getParent());
    $file_field->click();
    $this->assertTrue($assert_session->elementExists('css', '[name="new_storage_type"][value="file_upload"]')->isSelected());
    $page->pressButton('Continue');

    $page->fillField('label', 'file_upload');
    $this->assertNotEmpty($file_field = $page->find('css', '[name="group_field_options_wrapper"][value="file"]')->getParent());
    $file_field->click();
    $page->pressButton('Continue');

    $this->assertTrue($assert_session->elementExists('css', '[name="field_storage[subform][settings][uri_scheme]"][value="private"]')->isSelected());

    $page->pressButton('Save settings');
    $this->drupalGet('admin/structure/contact/manage/feedback/fields/contact_message.feedback.field_file_upload');
    $this->assertSession()->pageTextNotContains('It is advised to store file uploads for contact forms as private files');
  }

  /**
   * Tests that a warning is shown when the public scheme is selected on edit.
   */
  public function testFileFieldHasWarningForPublicSchemeOnEdit(): void {
    // Create and log in administrative user.
    $admin_user = $this->drupalCreateUser([
      'access site-wide contact form',
      'administer contact forms',
      'administer permissions',
      'administer users',
      'access site reports',
      'administer contact_message display',
      'administer contact_message fields',
      'administer contact_message form display',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/contact/manage/feedback/fields/add-field');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $page->find('xpath', '//input[@value="file_upload"]')->click();
    $this->assertNotEmpty($file_field = $page->find('css', '[name="new_storage_type"][value="file_upload"]')->getParent());
    $file_field->click();
    $this->assertTrue($assert_session->elementExists('css', '[name="new_storage_type"][value="file_upload"]')->isSelected());
    $page->pressButton('Continue');

    $page->fillField('label', 'file_upload');
    $this->assertNotEmpty($file_field = $page->find('css', '[name="group_field_options_wrapper"][value="file"]')->getParent());
    $file_field->click();
    $page->pressButton('Continue');

    $this->assertNotEmpty($uri_scheme_public_field = $page->find('css', 'input[type="radio"][name="field_storage[subform][settings][uri_scheme]"][value="public"]'));
    $uri_scheme_public_field->click();
    $assert_session->assertWaitOnAjaxRequest();

    $page->pressButton('Save settings');
    $this->drupalGet('admin/structure/contact/manage/feedback/fields/contact_message.feedback.field_file_upload');
    $this->assertSession()->pageTextContains('It is advised to store file uploads for contact forms as private files');
  }

  /**
   * Tests file scheme when private files are not configured.
   */
  public function testFileFieldHasPublicSchemeByDefaultWhenPrivateSchemeNotConfigured(): void {
    // Create and log in administrative user.
    $admin_user = $this->drupalCreateUser([
      'access site-wide contact form',
      'administer contact forms',
      'administer permissions',
      'administer users',
      'access site reports',
      'administer contact_message display',
      'administer contact_message fields',
      'administer contact_message form display',
    ]);
    $this->drupalLogin($admin_user);

    $this->drupalGet('admin/structure/contact/manage/feedback/fields/add-field');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $page->find('xpath', '//input[@value="file_upload"]')->click();
    $this->assertNotEmpty($file_field = $page->find('css', '[name="new_storage_type"][value="file_upload"]')->getParent());
    $file_field->click();
    $this->assertTrue($assert_session->elementExists('css', '[name="new_storage_type"][value="file_upload"]')->isSelected());
    $page->pressButton('Continue');

    $page->fillField('label', 'file_upload');
    $this->assertNotEmpty($file_field = $page->find('css', '[name="group_field_options_wrapper"][value="file"]')->getParent());
    $file_field->click();
    $page->pressButton('Continue');

    $this->assertTrue($this->assertSession()->elementExists('css', '[name="field_storage[subform][settings][uri_scheme]"][value="public"]')->isSelected());
    $this->assertSession()->pageTextContains('It is advised to store file uploads for contact forms as private files. You can configure this in settings.php');
  }

  /**
   * {@inheritdoc}
   */
  protected function writeSettings(array $settings) {
    if ($this->name() === 'testFileFieldHasPublicSchemeByDefaultWhenPrivateSchemeNotConfigured') {
      // Disable the private files scheme for the
      // testFileFieldHasPublicSchemeByDefaultWhenPrivateSchemeNotConfigured
      // test.
      unset($settings['settings']['file_private_path']);
    }
    parent::writeSettings($settings);
  }

}
