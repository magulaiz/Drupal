<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\file\Entity\File;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests for \Drupal\navigation\Form\SettingsForm.
 *
 * @group navigation
 */
class NavigationLogoTest extends BrowserTestBase {

  use TestFileCreationTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * A user with administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'navigation',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Inject the file_system and config.factory services.
    $this->fileSystem = $this->container->get('file_system');
    $this->configFactory = $this->container->get('config.factory');

    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'access navigation',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests Navigation logo configuration base options.
   */
  public function testSettingsLogoOptionsForm(): void {
    // Navigate to the settings form.
    $this->drupalGet('/admin/config/user-interface/navigation/settings');
    $this->assertSession()->statusCodeEquals(200);

    // Option 1. Check the default logo provider.
    $this->assertSession()->fieldValueEquals('logo_provider', 'default');
    $this->assertSession()->elementExists('css', 'a.admin-toolbar__logo > svg');

    // Option 2: Set the logo provider to hide and check.
    $edit = [
      'logo_provider' => 'hide',
    ];
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->elementNotExists('css', 'a.admin-toolbar__logo');

    // Option 3: Set the logo provider to custom and upload a logo.
    $file = current($this->getTestFiles('image'));
    $logo_file = File::create((array) $file + ['status' => 1]);
    $logo_file->save();
    $this->assertNotEmpty($logo_file, 'File entity is not empty.');

    $edit = [
      'logo_provider' => 'custom',
      'logo_path' => $logo_file->getFileUri(),
    ];
    $this->submitForm($edit, t('Save configuration'));
    // Refresh the page to verify custom logo is placed.
    $this->drupalGet('/admin/config/user-interface/navigation/settings');
    $this->assertSession()->elementExists('css', 'a.admin-toolbar__logo > img');
    $this->assertSession()->elementAttributeContains('css', 'a.admin-toolbar__logo > img', 'src', $logo_file->getFilename());

    //Option 4: Set the custom logo to an image in the source code.
    $edit = [
      'logo_provider' => 'custom',
      'logo_path' => 'core/misc/logo/drupal-logo.svg',
    ];
    $this->submitForm($edit, t('Save configuration'));
    // Refresh the page to verify custom logo is placed.
    $this->drupalGet('/admin/config/user-interface/navigation/settings');
    $this->assertSession()->elementExists('css', 'a.admin-toolbar__logo > img');
    $this->assertSession()->elementAttributeContains('css', 'a.admin-toolbar__logo > img', 'src', 'drupal-logo.svg');
  }

}
