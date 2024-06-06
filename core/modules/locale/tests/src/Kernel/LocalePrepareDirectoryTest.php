<?php

namespace Drupal\Tests\locale\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for locale_prepare_translations_directory().
 *
 * @group locale
 */
class LocalePrepareDirectoryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['language', 'locale', 'system'];

  /**
   * Checks if a translations directory gets created.
   */
  public function testPrepareDirectory() {

    $directory = Settings::get('file_public_path') . '/' . strtolower($this->randomMachineName(8));
    $this->config('locale.settings')
      ->set('translation.path', $directory)
      ->set('translation.use_source', LOCALE_TRANSLATION_USE_SOURCE_LOCAL)
      ->save();

    $this->assertDirectoryDoesNotExist($directory);
    locale_prepare_translations_directory();
    $this->assertDirectoryExists($directory);
  }

}
