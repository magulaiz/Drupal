<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\Tests\media\FunctionalJavascript\MediaSourceTestBase;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Tests the video media insertion on ckeditor.
 *
 * @group media
 */
class VideoMediaLibraryTest extends MediaSourceTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait;
  use CKEditor5TestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * The user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The media item to embed.
   *
   * @var \Drupal\media\MediaInterface
   */
  protected $media;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'media',
    'text',
    'media_library',
    'ckeditor5',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test format',
      'filters' => [
        'media_embed' => ['status' => TRUE],
      ],
    ])->save();
    Editor::create([
      'editor' => 'ckeditor5',
      'format' => 'test_format',
      'settings' => [
        'toolbar' => [
          'items' => [
            'drupalMedia',
          ],
        ],
        'plugins' => [
          'media_media' => [
            'allow_view_mode_override' => FALSE,
          ],
        ],
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

    // Create node type blog
    $this->drupalCreateContentType(['type' => 'blog']);

    $this->user = $this->drupalCreateUser(
      array_merge(self::$adminUserPermissions, [
        'use text format test_format',
        'create blog content',
      ])
    );
    $this->drupalLogin($this->user);
  }

  /**
   * Tests using drupalMedia button to embed media into CKEditor 5.
   */
  public function testVideoMediaAdding() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Creating video_type type media
    $source_id = 'video_file';
    $type_name = 'video_type';
    $field_name = 'field_media_' . $source_id;
    $this->doTestCreateMediaType($type_name, $source_id);

    // Enabling advanced UI inside Media Library settings page
    $this->drupalGet('admin/config/media/media-library');
    $page->checkField('advanced_ui');
    $page->pressButton('Save configuration');
    $assert_session->pageTextContains('The configuration options have been saved.');
    $video_preview_selector = '.ck-content .ck-widget.drupal-media .media video';

    // Create a video asset.
    file_put_contents('public://file.mp4', str_repeat('t', 10));
    $file = File::create([
      'uri' => 'public://file.mp4',
      'filename' => 'file.mp4',
    ]);
    $file->save();

    // Disabling "Display" field option
    $this->drupalGet('admin/structure/media/manage/' . $type_name . '/fields/media.' . $type_name . '.' . $field_name);
    if ($page->hasCheckedField('field_storage[subform][settings][display_field]')) {
      $page->uncheckField('field_storage[subform][settings][display_field]');
      $assert_session->assertWaitOnAjaxRequest();
      $page->pressButton('Save settings');
      $assert_session->pageTextContains('Saved Video file configuration.');
    }

    // Creating a blog and adding video_type media to the editor
    $this->drupalGet('/node/add/blog');
    $this->waitForEditor();
    $this->pressEditorButton('Insert Media');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#drupal-modal #media-library-content'));
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', 'input[name="files[upload][]"]'));
    $page->attachFileToField("files[upload][]", \Drupal::service('file_system')->realpath('public://file.mp4'));
    $this->assertNotEmpty($assert_session->waitForButton('Save and insert', 1000));
    $page->find('css', '.ui-dialog-buttonset')->pressButton('Save and insert');    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', $video_preview_selector, 1000),
      'Video element not found inside the editor'
    );

    // Enabling "Display" field and Files displayed by default options
    $this->drupalGet('admin/structure/media/manage/' . $type_name . '/fields/media.' . $type_name . '.' . $field_name);
    if ($page->hasUncheckedField('field_storage[subform][settings][display_field]')) {
      $page->checkField('field_storage[subform][settings][display_field]');
      $assert_session->assertWaitOnAjaxRequest();
    }
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', 'input[name="field_storage[subform][settings][display_default]"]'));
    if ($page->hasUncheckedField('field_storage[subform][settings][display_default]')) {
      $page->checkField('field_storage[subform][settings][display_default]');
      $assert_session->assertWaitOnAjaxRequest();
    }
    $page->pressButton('Save settings');
    $assert_session->pageTextContains('Saved Video file configuration.');

    // Creating a blog and adding video_type media to the editor
    $this->drupalGet('/node/add/blog');
    $this->waitForEditor();
    $this->pressEditorButton('Insert Media');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#drupal-modal #media-library-content'));
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', 'input[name="files[upload][]"]'));
    $page->attachFileToField("files[upload][]", \Drupal::service('file_system')->realpath('public://file.mp4'));
    $this->assertNotEmpty($assert_session->waitForButton('Save and insert', 1000));
    $page->find('css', '.ui-dialog-buttonset')->pressButton('Save and insert');
    $this->assertNotEmpty(
      $assert_session->waitForElementVisible('css', $video_preview_selector, 1000),
      'Video element not found inside the editor'
    );
  }

}
