<?php

namespace Drupal\Tests\ckeditor5\Functional;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;

/**
 * Tests the upgrade path for CKEditor 5 alignment.
 *
 * @group Update
 */
class CKEditor5UpdateAlignmentTest extends UpdatePathTestBase {

  use CKEditor5TestTrait;

  protected static $modules = [
    'editor',
    'ckeditor5',
    'filter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../system/tests/fixtures/update/drupal-9.3.0.filled.standard.php.gz',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test format',
      'filters' => [
        'filter_html' => [
          'status' => TRUE,
          'settings' => [
            'allowed_html' => '<p class="text-align-center">',
          ],
        ],
        'filter_align' => ['status' => TRUE],
        'filter_caption' => ['status' => TRUE],
      ],
    ])->save();
    Editor::create([
      'editor' => 'ckeditor5',
      'format' => 'test_format',
      'settings' => [
        'toolbar' => [
          'items' => [
            'sourceEditing',
            'link',
            'bold',
            'italic',
            'alignment:center',
          ],
        ],
        'plugins' => [
          'ckeditor5_sourceEditing' => [
            'allowed_tags' => [],
          ],
        ],
      ],
      'image_upload' => [
        'status' => FALSE,
      ],
    ])->save();
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    $this->user = $this->drupalCreateUser([
      'use text format test_format',
      'access toolbar',
      'administer site configuration',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Tests that CKEditor 5 alignment configurations that are individual buttons
   * are updated to be in dropdown form in the toolbar.
   */
  public function testUpdateAlignmentButtons() {
    $expected_toolbar_items = [
      'sourceEditing',
      'link',
      'bold',
      'italic',
      'alignment',
    ];
    $expected_alignment_plugin = [
      'enabled_alignments' => [
        'center',
        ],
      ];
    $this->runUpdates();
    $editor = Editor::load('test_format');
    $settings = $editor->getSettings();
    $this->assertEquals($expected_toolbar_items, $settings['toolbar']['items']);
    $this->assertEquals($expected_alignment_plugin, $settings['plugins']['ckeditor5_alignment']);
  }

}
