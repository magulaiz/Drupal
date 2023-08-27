<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\editor\EditorInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Prophet;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language
 * @group ckeditor5
 * @internal
 */
class LanguagePluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public static function providerGetDynamicPluginConfig(): array {
    $un_language_list = LanguageManager::getUnitedNationsLanguageList();
    $standard_language_list = LanguageManager::getStandardLanguageList();
    $enabled_language_list = [
      'en' => ['English', 'English'],
      // cSpell:disable-next-line.
      'mi' => ['Maori', 'Te Reo Māori'],
    ];
    return [
      'un' => [
        ['language_list' => 'un'],
        [
          'language' => [
            'textPartLanguage' => static::buildExpectedDynamicConfig($un_language_list),
          ],
        ],
        static::buildMockLanguageManager($un_language_list),
      ],
      'all' => [
        ['language_list' => 'all'],
        [
          'language' => [
            'textPartLanguage' => static::buildExpectedDynamicConfig($standard_language_list),
          ],
        ],
        static::buildMockLanguageManager($standard_language_list),
      ],
      'enabled' => [
        ['language_list' => 'enabled'],
        [
          'language' => [
            'textPartLanguage' => static::buildExpectedDynamicConfig($enabled_language_list),
          ],
        ],
        static::buildMockLanguageManager($enabled_language_list),
      ],
      'default configuration' => [
        [],
        [
          'language' => [
            'textPartLanguage' => static::buildExpectedDynamicConfig($un_language_list),
          ],
        ],
        static::buildMockLanguageManager($un_language_list),
      ],
    ];
  }

  /**
   * Builds the expected dynamic configuration output given a language list.
   *
   * @param array $language_list
   *   The languages list from the language manager.
   *
   * @return array
   *   The expected output of the dynamic plugin configuration.
   */
  protected static function buildExpectedDynamicConfig(array $language_list) {
    $expected_language_config = [];
    foreach ($language_list as $language_code => $language_list_item) {
      $item = [
        'title' => $language_list_item[0],
        'languageCode' => $language_code,
      ];
      if (isset($language_list_item[2])) {
        $item['textDirection'] = $language_list_item[2];
      }
      $expected_language_config[$item['title']] = $item;
    }
    ksort($expected_language_config);
    return array_values($expected_language_config);
  }

  /**
   * Build a mock language manager with mocked languages.
   *
   * @param array $language_list
   *   A language list as returned from Language Manager service.
   *
   * @return \Drupal\language\ConfigurableLanguageManagerInterface
   *   A mocked language manager.
   */
  protected static function buildMockLanguageManager(array $language_list) {
    $mock_languages = [];
    foreach ($language_list as $language_code => $language_list_item) {
      // LanguageInterface::getDirection() returns ::DIRECTION_LTR when unset.
      if (!isset($language_list_item[2])) {
        $language_list_item[2] = LanguageInterface::DIRECTION_LTR;
      }

      $mock_language = (new Prophet())->prophesize(LanguageInterface::class);
      $mock_language->getId()->willReturn($language_code);
      $mock_language->getName()->willReturn($language_list_item[0]);
      $mock_language->getDirection()
        ->willReturn($language_list_item[2]);
      $mock_languages[$language_code] = $mock_language->reveal();
    }
    $mock_language_manager = (new Prophet())->prophesize(ConfigurableLanguageManagerInterface::class);
    $mock_language_manager->getLanguages()->willReturn($mock_languages);
    return $mock_language_manager->reveal();
  }

  /**
   * @covers ::getDynamicPluginConfig
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration, array $expected_dynamic_config, LanguageManagerInterface $language_manager): void {
    $plugin = new Language($configuration, 'ckeditor5_language', NULL, $language_manager);
    $dynamic_config = $plugin->getDynamicPluginConfig([], (new Prophet())->prophesize(EditorInterface::class)
      ->reveal());
    $this->assertSame($expected_dynamic_config, $dynamic_config);
  }

}
