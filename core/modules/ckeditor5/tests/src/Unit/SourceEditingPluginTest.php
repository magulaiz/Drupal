<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\SourceEditing;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\SourceEditing
 * @group ckeditor5
 * @internal
 */
class SourceEditingPluginTest extends UnitTestCase {

  /**
   * Provides a list of configs to test.
   */
  public function providerGetDynamicPluginConfig(): array {
    return [
      'Default data set' => [
        [
          'allowed_tags' => ['<cite> <dl> <dt> <dd> <a hreflang> <blockquote cite> <ul type> <ol start type> <h2 id> <h3 id> <h4 id> <h5 id> <h6 id>'],
        ],
        [
          ['name' => 'cite'],
          ['name' => 'dl'],
          ['name' => 'dt'],
          ['name' => 'dd'],
          [
            'name' => 'a',
            'attributes' => [['key' => 'hreflang', 'value' => TRUE]],
          ],
          [
            'name' => 'blockquote',
            'attributes' => [['key' => 'cite', 'value' => TRUE]],
          ],
          [
            'name' => 'ul',
            'attributes' => [['key' => 'type', 'value' => TRUE]],
          ],
          [
            'name' => 'ol',
            'attributes' => [
              ['key' => 'start', 'value' => TRUE],
              ['key' => 'type', 'value' => TRUE],
            ],
          ],
          [
            'name' => 'h2',
            'attributes' => [['key' => 'id', 'value' => TRUE]],
          ],
          [
            'name' => 'h3',
            'attributes' => [['key' => 'id', 'value' => TRUE]],
          ],
          [
            'name' => 'h4',
            'attributes' => [['key' => 'id', 'value' => TRUE]],
          ],
          [
            'name' => 'h5',
            'attributes' => [['key' => 'id', 'value' => TRUE]],
          ],
          [
            'name' => 'h6',
            'attributes' => [['key' => 'id', 'value' => TRUE]],
          ],
        ],
      ],
    ];
  }

  /**
   * @covers ::getDynamicPluginConfig
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration, array $expected_html_tags): void {
    $plugin = new SourceEditing($configuration, 'ckeditor5_sourceEditing', NULL);
    $config = $plugin->getDynamicPluginConfig([], $this->prophesize(Editor::class)
      ->reveal());
    $this->assertArrayHasKey('htmlSupport', $config);
    $this->assertArrayHasKey('allow', $config['htmlSupport']);
    foreach ($expected_html_tags as $expected_html_tag) {
      $this->assertContains($expected_html_tag, $config['htmlSupport']['allow']);
    }
    $this->assertSameSize($expected_html_tags, $config['htmlSupport']['allow']);
  }

}
