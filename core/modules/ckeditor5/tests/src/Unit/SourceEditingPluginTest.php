<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\Plugin\CKEditor5Plugin\SourceEditing;
use Drupal\editor\Entity\Editor;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\SourceEditing
 * @group ckeditor5
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
      ],
    ];
  }

  /**
   * @covers ::getDynamicPluginConfig
   *
   * @dataProvider providerGetDynamicPluginConfig
   */
  public function testGetDynamicPluginConfig(array $configuration): void {
    $plugin = new SourceEditing($configuration, 'ckeditor5_sourceEditing', NULL);
    $config = $plugin->getDynamicPluginConfig([], $this->prophesize(Editor::class)
      ->reveal());
    $this->assertArrayHasKey('htmlSupport', $config);
    $this->assertArrayHasKey('allow', $config['htmlSupport']);
    $this->assertContains(['name' => 'cite'], $config['htmlSupport']['allow']);
    $this->assertContains(['name' => 'dl'], $config['htmlSupport']['allow']);
    $this->assertContains(['name' => 'dt'], $config['htmlSupport']['allow']);
    $this->assertContains(['name' => 'dd'], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'a',
      'attributes' => [['key' => 'hreflang', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'blockquote',
      'attributes' => [['key' => 'cite', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'ul',
      'attributes' => [['key' => 'type', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'ol',
      'attributes' => [
        ['key' => 'start', 'value' => TRUE],
        ['key' => 'type', 'value' => TRUE],
      ],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'h2',
      'attributes' => [['key' => 'id', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'h3',
      'attributes' => [['key' => 'id', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'h4',
      'attributes' => [['key' => 'id', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'h5',
      'attributes' => [['key' => 'id', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
    $this->assertContains([
      'name' => 'h6',
      'attributes' => [['key' => 'id', 'value' => TRUE]],
    ], $config['htmlSupport']['allow']);
  }

}
