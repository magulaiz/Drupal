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
    $session = $this->getSession();
    $session->getPage()->fillField('label', 'file upload');
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
    $session = $this->getSession();
    $session->getPage()->fillField('label', 'file_upload');
    $session->getPage()
      ->find('xpath', '//input[@value="file_upload"]')
      ->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $session->getPage()->find('xpath', '//input[@value="file"]')->click();
    $session->getPage()->pressButton('Continue');
    $this->assertTrue($this->assertSession()
      ->elementExists('css', '[name="field_storage[subform][settings][uri_scheme]"][value="public"]')
      ->isSelected());
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
