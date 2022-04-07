<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\Heading;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language
 * @group ckeditor5
 * @internal
 */
class HeadingPluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public function providerGetDynamicPluginConfig(): array {
    return [
      'All headings' => [
        Heading::DEFAULT_CONFIGURATION,
      ],
      'Only required headings' => [
        [
          'enabled_headings' => [],
        ],
      ],
      'Heading 2 only' => [
        [
          'enabled_headings' => [
            'heading2',
          ],
        ],
      ],
      'Heading 2 and 3 only' => [
        [
          'enabled_headings' => [
            'heading2',
            'heading3',
          ],
        ],
      ],
    ];
  }

  /**
   * @covers ::validChoices
   *
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration): void {
    $this->assertArrayHasKey('enabled_headings', $configuration);

    // Retrieve the possible heading options from the ckeditor5 config.
    $ckeditor5_config = Yaml::parseFile('core/modules/ckeditor5/ckeditor5.ckeditor5.yml');
    $configuration['heading'] = $ckeditor5_config['ckeditor5_heading']['ckeditor5']['config']['heading'];

    // Build the dynamic configuration based on the enabled headings.
    $plugin = new Heading($configuration, 'ckeditor5_heading', NULL);
    $config = $plugin->getDynamicPluginConfig($configuration, $this->prophesize(Editor::class)
      ->reveal());

    // Check that the generated configuration contains all enabled headings.
    $enabled_headings = array_merge($plugin::ALWAYS_ENABLED_HEADINGS, $configuration['enabled_headings']);
    $this->assertSame(count($config['heading']['options']), count($enabled_headings));
    foreach ($config['heading']['options'] as $heading) {
      $this->assertContains($heading['model'], $enabled_headings);
    }
  }

}
