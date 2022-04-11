<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language;
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
    $un_languages = [
      [
        'title' => 'Arabic',
        'languageCode' => 'ar',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Chinese, Simplified',
        'languageCode' => 'zh-hans',
      ],
      [
        'title' => 'English',
        'languageCode' => 'en',
      ],
      [
        'title' => 'French',
        'languageCode' => 'fr',
      ],
      [
        'title' => 'Russian',
        'languageCode' => 'ru',
      ],
      [
        'title' => 'Spanish',
        'languageCode' => 'es',
      ],
    ];
    $standard_languages = [
      [
        'title' => 'Afrikaans',
        'languageCode' => 'af',
      ],
      [
        'title' => 'Albanian',
        'languageCode' => 'sq',
      ],
      [
        'title' => 'Amharic',
        'languageCode' => 'am',
      ],
      [
        'title' => 'Arabic',
        'languageCode' => 'ar',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Armenian',
        'languageCode' => 'hy',
      ],
      [
        'title' => 'Asturian',
        'languageCode' => 'ast',
      ],
      [
        'title' => 'Azerbaijani',
        'languageCode' => 'az',
      ],
      [
        'title' => 'Bahasa Malaysia',
        'languageCode' => 'ms',
      ],
      [
        'title' => 'Basque',
        'languageCode' => 'eu',
      ],
      [
        'title' => 'Belarusian',
        'languageCode' => 'be',
      ],
      [
        'title' => 'Bengali',
        'languageCode' => 'bn',
      ],
      [
        'title' => 'Bosnian',
        'languageCode' => 'bs',
      ],
      [
        'title' => 'Bulgarian',
        'languageCode' => 'bg',
      ],
      [
        'title' => 'Burmese',
        'languageCode' => 'my',
      ],
      [
        'title' => 'Catalan',
        'languageCode' => 'ca',
      ],
      [
        'title' => 'Chinese, Simplified',
        'languageCode' => 'zh-hans',
      ],
      [
        'title' => 'Chinese, Traditional',
        'languageCode' => 'zh-hant',
      ],
      [
        'title' => 'Croatian',
        'languageCode' => 'hr',
      ],
      [
        'title' => 'Czech',
        'languageCode' => 'cs',
      ],
      [
        'title' => 'Danish',
        'languageCode' => 'da',
      ],
      [
        'title' => 'Dutch',
        'languageCode' => 'nl',
      ],
      [
        'title' => 'Dzongkha',
        'languageCode' => 'dz',
      ],
      [
        'title' => 'English',
        'languageCode' => 'en',
      ],
      [
        'title' => 'Esperanto',
        'languageCode' => 'eo',
      ],
      [
        'title' => 'Estonian',
        'languageCode' => 'et',
      ],
      [
        'title' => 'Faeroese',
        'languageCode' => 'fo',
      ],
      [
        'title' => 'Filipino',
        'languageCode' => 'fil',
      ],
      [
        'title' => 'Finnish',
        'languageCode' => 'fi',
      ],
      [
        'title' => 'French',
        'languageCode' => 'fr',
      ],
      [
        'title' => 'Frisian, Western',
        'languageCode' => 'fy',
      ],
      [
        'title' => 'Galician',
        'languageCode' => 'gl',
      ],
      [
        'title' => 'Georgian',
        'languageCode' => 'ka',
      ],
      [
        'title' => 'German',
        'languageCode' => 'de',
      ],
      [
        'title' => 'Greek',
        'languageCode' => 'el',
      ],
      [
        'title' => 'Gujarati',
        'languageCode' => 'gu',
      ],
      [
        'title' => 'Haitian Creole',
        'languageCode' => 'ht',
      ],
      [
        'title' => 'Hebrew',
        'languageCode' => 'he',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Hindi',
        'languageCode' => 'hi',
      ],
      [
        'title' => 'Hungarian',
        'languageCode' => 'hu',
      ],
      [
        'title' => 'Icelandic',
        'languageCode' => 'is',
      ],
      [
        'title' => 'Indonesian',
        'languageCode' => 'id',
      ],
      [
        'title' => 'Irish',
        'languageCode' => 'ga',
      ],
      [
        'title' => 'Italian',
        'languageCode' => 'it',
      ],
      [
        'title' => 'Japanese',
        'languageCode' => 'ja',
      ],
      [
        'title' => 'Javanese',
        'languageCode' => 'jv',
      ],
      [
        'title' => 'Kannada',
        'languageCode' => 'kn',
      ],
      [
        'title' => 'Kazakh',
        'languageCode' => 'kk',
      ],
      [
        'title' => 'Khmer',
        'languageCode' => 'km',
      ],
      [
        'title' => 'Korean',
        'languageCode' => 'ko',
      ],
      [
        'title' => 'Kurdish',
        'languageCode' => 'ku',
      ],
      [
        'title' => 'Kyrgyz',
        'languageCode' => 'ky',
      ],
      [
        'title' => 'Lao',
        'languageCode' => 'lo',
      ],
      [
        'title' => 'Latvian',
        'languageCode' => 'lv',
      ],
      [
        'title' => 'Lithuanian',
        'languageCode' => 'lt',
      ],
      [
        'title' => 'Lolspeak',
        'languageCode' => 'xx-lolspeak',
      ],
      [
        'title' => 'Macedonian',
        'languageCode' => 'mk',
      ],
      [
        'title' => 'Malagasy',
        'languageCode' => 'mg',
      ],
      [
        'title' => 'Malayalam',
        'languageCode' => 'ml',
      ],
      [
        'title' => 'Marathi',
        'languageCode' => 'mr',
      ],
      [
        'title' => 'Mongolian',
        'languageCode' => 'mn',
      ],
      [
        'title' => 'Nepali',
        'languageCode' => 'ne',
      ],
      [
        'title' => 'Northern Sami',
        'languageCode' => 'se',
      ],
      [
        'title' => 'Norwegian Bokmål',
        'languageCode' => 'nb',
      ],
      [
        'title' => 'Norwegian Nynorsk',
        'languageCode' => 'nn',
      ],
      [
        'title' => 'Occitan',
        'languageCode' => 'oc',
      ],
      [
        'title' => 'Persian, Farsi',
        'languageCode' => 'fa',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Polish',
        'languageCode' => 'pl',
      ],
      [
        'title' => 'Portuguese, Brazil',
        'languageCode' => 'pt-br',
      ],
      [
        'title' => 'Portuguese, Portugal',
        'languageCode' => 'pt-pt',
      ],
      [
        'title' => 'Punjabi',
        'languageCode' => 'pa',
      ],
      [
        'title' => 'Romanian',
        'languageCode' => 'ro',
      ],
      [
        'title' => 'Russian',
        'languageCode' => 'ru',
      ],
      [
        'title' => 'Scots',
        'languageCode' => 'sco',
      ],
      [
        'title' => 'Scots Gaelic',
        'languageCode' => 'gd',
      ],
      [
        'title' => 'Serbian',
        'languageCode' => 'sr',
      ],
      [
        'title' => 'Simple English',
        'languageCode' => 'en-x-simple',
      ],
      [
        'title' => 'Sinhala',
        'languageCode' => 'si',
      ],
      [
        'title' => 'Slovak',
        'languageCode' => 'sk',
      ],
      [
        'title' => 'Slovenian',
        'languageCode' => 'sl',
      ],
      [
        'title' => 'Spanish',
        'languageCode' => 'es',
      ],
      [
        'title' => 'Swahili',
        'languageCode' => 'sw',
      ],
      [
        'title' => 'Swedish',
        'languageCode' => 'sv',
      ],
      [
        'title' => 'Swiss German',
        'languageCode' => 'gsw-berne',
      ],
      [
        'title' => 'Tamil',
        'languageCode' => 'ta',
      ],
      [
        'title' => 'Tamil, Sri Lanka',
        'languageCode' => 'ta-lk',
      ],
      [
        'title' => 'Telugu',
        'languageCode' => 'te',
      ],
      [
        'title' => 'Thai',
        'languageCode' => 'th',
      ],
      [
        'title' => 'Tibetan',
        'languageCode' => 'bo',
      ],
      [
        'title' => 'Turkish',
        'languageCode' => 'tr',
      ],
      [
        'title' => 'Tuvan',
        'languageCode' => 'tyv',
      ],
      [
        'title' => 'Ukrainian',
        'languageCode' => 'uk',
      ],
      [
        'title' => 'Urdu',
        'languageCode' => 'ur',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Uyghur',
        'languageCode' => 'ug',
        'textDirection' => 'rtl',
      ],
      [
        'title' => 'Vietnamese',
        'languageCode' => 'vi',
      ],
      [
        'title' => 'Welsh',
        'languageCode' => 'cy',
      ],
    ];

    return [
      [
        ['language_list' => 'un'],
        [
          'language' => [
            'textPartLanguage' => $un_languages,
          ],
        ],
      ],
      [
        ['language_list' => 'all'],
        [
          'language' => [
            'textPartLanguage' => $standard_languages,
          ],
        ],
      ],
      // Default configuration.
      [
        [],
        [
          'language' => [
            'textPartLanguage' => $un_languages,
          ],
        ],
      ],
    ];
  }

  /**
   * @covers ::getDynamicPluginConfig
   *
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration, array $expected_dynamic_config): void {
    $plugin = new Language($configuration, 'ckeditor5_language', NULL);
    $dynamic_config = $plugin->getDynamicPluginConfig([], $this->prophesize(Editor::class)
      ->reveal());
    $this->assertSame($expected_dynamic_config, $dynamic_config);
  }

}
