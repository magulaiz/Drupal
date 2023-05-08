<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\CodeBlock;
use Drupal\editor\EditorInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\CodeBlock
 * @group ckeditor5
 * @internal
 */
class CodeBlockPluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public function providerGetDynamicPluginConfig(): array {
    // Uses static configuration from the ckeditor5.ckeditor5.yml file.
    $static_plugin_config = $this->getStaticPluginConfig();
    $all_languages = $static_plugin_config['codeBlock']['languages'];
    $all_language_ids = array_column($all_languages, 'language');
    $default_language_ids = CodeBlock::DEFAULT_CONFIGURATION['enabled_languages'];
    // Remove the plaintext option because that is always set.
    if (($key = array_search('plaintext', $all_language_ids)) !== FALSE) {
      unset($all_language_ids[$key]);
    }

    $return = [
      'All languages' => [
        [
          'enabled_languages' => $all_language_ids,
        ],
        [
          'codeBlock' => [
            'languages' => $all_languages,
          ],
        ],
      ],
      'Default languages' => [
        [
          'enabled_languages' => $default_language_ids,
        ],
        [
          'codeBlock' => [
            'languages' => array_values(
              array_filter(
                $all_languages,
                function ($option) use ($default_language_ids) {
                  return in_array(
                    $option['language'],
                    array_merge(
                      ['plaintext'],
                      $default_language_ids
                    ),
                    TRUE);
                }
              )
            ),
          ],
        ],
      ],
      'No languages allowed' => [
        [
          'enabled_languages' => [],
        ],
        [
          'codeBlock' => [
            'languages' => [
              ['language' => 'plaintext', 'label' => 'Plain text'],
            ],
          ],
        ],
      ],
      'Php only' => [
        [
          'enabled_languages' => ['php'],
        ],
        [
          'codeBlock' => [
            'languages' => array_values(
              array_filter(
                $all_languages,
                function ($option) {
                  return in_array(
                    $option['language'],
                    ['php', 'plaintext'],
                    TRUE
                  );
                }
              )
            ),
          ],
        ],
      ],
    ];
    return $return;
  }

  /**
   * @covers ::getDynamicPluginConfig
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration, array $expected_dynamic_config): void {
    $plugin = new CodeBlock($configuration, 'ckeditor5_codeBlock', NULL);
    $dynamic_plugin_config = $plugin->getDynamicPluginConfig($this->getStaticPluginConfig(), $this->prophesize(EditorInterface::class)
      ->reveal());
    $this->assertSame($expected_dynamic_config, $dynamic_plugin_config);
  }

  /**
   * Returns static plugin configuration.
   *
   * @return array
   *   The static configuration array.
   */
  private function getStaticPluginConfig(): array {
    $ckeditor5_plugin_definitions = Yaml::parseFile(__DIR__ . '/../../../ckeditor5.ckeditor5.yml');
    return $ckeditor5_plugin_definitions['ckeditor5_codeBlock']['ckeditor5']['config'];
  }

}
