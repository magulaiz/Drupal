<?php

namespace Drupal\Tests\contact\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests file fields when added to contact forms.
 *
 * @see \Drupal\Tests\contact\Functional\ContactStorageTest
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
   * Tests configuration options and the site-wide contact form.
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
    $session = $this->getSession();
    $session->getPage()->fillField('label', 'fileupload');
    $session->getPage()
      ->find('xpath', '//input[@value="file_upload"]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $session->getPage()->find('xpath', '//input[@value="file"]')->click();
    $session->getPage()->pressButton('Continue');
    $this->assertTrue($this->assertSession()
      ->elementExists('css', '[name="field_storage[subform][settings][uri_scheme]"][value="private"]')
      ->isSelected());
  }

  /**
   * Tests configuration options and the site-wide contact form.
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
    $session = $this->getSession();
    $session->getPage()->fillField('label', 'fileupload');
    $session->getPage()
      ->find('xpath', '//input[@value="file_upload"]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $session->getPage()->find('xpath', '//input[@value="file"]')->click();
    $session->getPage()->pressButton('Continue');
    $this->assertTrue($this->assertSession()
      ->elementExists('css', '[name="field_storage[subform][settings][uri_scheme]"][value="public"]')
      ->isSelected());
    $this->assertSession()->pageTextContains('It is advised to store file uploads for contact forms as private files. Please configure this in settings.php');
  }

  /**
   * {@inheritDoc}
   */
  protected function writeSettings(array $settings) {
    if ($this->getName() === 'testFileFieldHasPublicSchemeByDefaultWhenPrivateSchemeNotConfigured') {
      // Disable the private files scheme.
      unset($settings['settings']['file_private_path']);
    }
    parent::writeSettings($settings);
  }

}
