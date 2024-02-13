<?php

namespace Drupal\Tests\locale\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\locale\Gettext;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests translation update's effects on configuration translations.
 *
 * @group locale
 */
class ModuleInstallConfigTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['language', 'locale', 'config_translation', 'content_translation', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests configuration translations after any module/theme install.
   */
  public function testConfigTranslationImport(): void {
    $admin_user = $this->drupalCreateUser([
      'administer modules',
      'administer site configuration',
      'administer languages',
      'access administration pages',
      'administer permissions',
      'administer account settings',
      'administer nodes',
      'administer content types',
      'translate interface',
    ]);
    $this->drupalLogin($admin_user);

    // Add a language.
    ConfigurableLanguage::createFromLangcode('de')->save();

    // Enable import of translations. By default this is disabled for automated
    // tests.
    $this->config('locale.settings')
      ->set('translation.import_enabled', TRUE)
      ->set('translation.use_source', LOCALE_TRANSLATION_USE_SOURCE_LOCAL)
      ->save();

    // Set the default language to something other than English.
    $this->config('system.site')->set('default_langcode', 'de')->save();

    $this->importPoFile($this->getPoFileWithConfigDe(), 'de');
    // Install book module to test the configuration language code.
    $this->drupalGet('admin/modules');
    $edit = ['modules[book][enable]' => TRUE];
    $this->submitForm($edit, 'Install');
    // Test this book config edit page for translation overrides or not.
    $this->drupalGet('admin/structure/types/manage/book');
    $this->drupalGet('/en/admin/structure/types/manage/book');
    $this->assertSession()->fieldValueEquals('name', 'Book page');
  }

  /**
   * Helper function: import a standalone .po file in a given language.
   *
   * @param string $contents
   *   Contents of the .po file to import.
   * @param string $langcode
   *   The langcode for the .po file.
   */
  public function importPoFile(string $contents, string $langcode): void {
    $file_system = \Drupal::service('file_system');
    $name = $file_system->tempnam('temporary://', "po_") . '.po';
    file_put_contents($name, $contents);

    $file = (object) [
      'langcode' => $langcode,
      'uri' => $name,
    ];

    Gettext::fileToDatabase($file, []);

    $file_system->unlink($name);
  }

  /**
   * Helper function that returns a .po file with configuration translations.
   */
  public function getPoFileWithConfigDe() {
    return <<< EOF
      msgid ""
      msgstr ""
      "Project-Id-Version: Drupal 8\\n"
      "MIME-Version: 1.0\\n"
      "Content-Type: text/plain; charset=UTF-8\\n"
      "Content-Transfer-Encoding: 8bit\\n"
      "Plural-Forms: nplurals=2; plural=(n > 1);\\n"

      msgid "Anonymous"
      msgstr "Anonymous German"

      msgid "German"
      msgstr "Deutsch"

      msgid "Book page"
      msgstr "Book page german"

      EOF;
  }

}
