<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\ListPlugin;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\ListPlugin
 * @group ckeditor5
 * @internal
 */
class ListPluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public function providerGetDynamicPluginConfig(): array {
    return [
      'startIndex is false' => [
        [
          'reversed' => TRUE,
          'startIndex' => FALSE,
        ],
        [
          'list' => [
          'properties' => [
            'reversed' => TRUE,
            'startIndex' => FALSE,
            ],
          ],
        ],
      ],
      'reversed is false' => [
        [
          'reversed' => FALSE,
          'startIndex' => TRUE,
        ],
        [
          'list' => [
          'properties' => [
            'reversed' => FALSE,
            'startIndex' => TRUE,
            ],
          ],
        ],
      ],
      'both disabled' => [
        [
          'reversed' => FALSE,
          'startIndex' => FALSE,
        ],
        [
          'list' => [
          'properties' => [
            'reversed' => FALSE,
            'startIndex' => FALSE,
            ],
          ],
        ],
      ],
      'both enabled' => [
        [
          'reversed' => TRUE,
          'startIndex' => TRUE,
        ],
        [
          'list' => [
          'properties' => [
            'reversed' => TRUE,
            'startIndex' => TRUE,
            ],
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
    $plugin = new ListPlugin($configuration, 'ckeditor5_list', NULL);
    $dynamic_config = $plugin->getDynamicPluginConfig([], $this->prophesize(Editor::class)
      ->reveal());
    $this->assertSame($expected_dynamic_config, $dynamic_config);
  }

}
