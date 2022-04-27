<?php

declare(strict_types = 1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language;
use Drupal\Core\Language\LanguageManager;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language
 * @group ckeditor5
 * @internal
 */
class LanguagePluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public function providerGetDynamicPluginConfig(): array {
    return [
      [['language_list' => 'un'], LanguageManager::getUnitedNationsLanguageList()],
      [['language_list' => 'all'], LanguageManager::getStandardLanguageList()],
      // Default configuration.
      [[], LanguageManager::getUnitedNationsLanguageList()],
    ];
  }

  /**
   * @covers ::getDynamicPluginConfig
   *
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration, array $expected_languages): void {
    $plugin = new Language($configuration, 'ckeditor5_language', NULL);
    $config = $plugin->getDynamicPluginConfig([], $this->prophesize(Editor::class)->reveal());
    $this->assertArrayHasKey('language', $config);
    $this->assertArrayHasKey('textPartLanguage', $config['language']);
    $this->assertContains(['title' => 'Arabic', 'languageCode' => 'ar', 'textDirection' => 'rtl'], $config['language']['textPartLanguage']);
    $this->assertContains(['title' => 'Chinese, Simplified', 'languageCode' => 'zh-hans'], $config['language']['textPartLanguage']);
    $this->assertContains(['title' => 'English', 'languageCode' => 'en'], $config['language']['textPartLanguage']);
    $this->assertContains(['title' => 'French', 'languageCode' => 'fr'], $config['language']['textPartLanguage']);
    $this->assertContains(['title' => 'Russian', 'languageCode' => 'ru'], $config['language']['textPartLanguage']);
    $this->assertContains(['title' => 'Spanish', 'languageCode' => 'es'], $config['language']['textPartLanguage']);
    $this->assertSameSize($expected_languages, $config['language']['textPartLanguage']);
  }

}
