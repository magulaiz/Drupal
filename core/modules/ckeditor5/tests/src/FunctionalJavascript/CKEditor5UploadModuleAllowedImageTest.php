<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\Tests\TestFileCreationTrait;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Tests that it's possible to upload SVG image, with the test module enabled.
 *
 * @group ckeditor5
 * @internal
 */
class CKEditor5UploadModuleAllowedImageTest extends CKEditor5TestBase {

  use CKEditor5TestTrait;
  use TestFileCreationTrait;

  /**
   * The user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A host entity with a body field to embed images in.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $host;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ckeditor5',
    'node',
    'text',
    'ckeditor5_test_module_allowed_image',
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
        'filter_html' => [
          'status' => TRUE,
          'settings' => [
            'allowed_html' => '<p> <br> <img alt height width src data-entity-uuid data-entity-type>',
          ],
        ],
      ],
    ])->save();
    Editor::create([
      'editor' => 'ckeditor5',
      'format' => 'test_format',
      'settings' => [
        'toolbar' => [
          'items' => [
            'drupalInsertImage',
          ],
        ],
        'plugins' => [
          'ckeditor5_imageResize' => [
            'allow_resize' => TRUE,
          ],
        ],
      ],
      'image_upload' => [
        'status' => TRUE,
        'scheme' => 'public',
        'directory' => 'inline-images',
        'max_size' => '1M',
        'max_dimensions' => ['width' => 100, 'height' => 100],
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
    $this->adminUser = $this->drupalCreateUser([
      'use text format test_format',
      'bypass node access',
      'administer filters',
    ]);

    $this->host = $this->createNode([
      'type' => 'page',
      'title' => 'Animals with strange names',
      'body' => [
        'value' => '<p>The pirate is irate.</p>',
        'format' => 'test_format',
      ],
    ]);
    $this->host->save();

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests that it's possible to upload SVG image, with the test module enabled.
   */
  public function testCanUploadSvg(): void {
    $page = $this->getSession()->getPage();

    $src = 'core/modules/ckeditor5/tests/fixtures/test-svg-upload.svg';

    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();

    // @see \Drupal\Tests\ckeditor5\FunctionalJavascript\ImageTest::addImage()
    $this->assertNotEmpty($image_upload_field = $page->find('css', '.ck-file-dialog-button input[type="file"]'));
    $image_upload_field->attachFile($this->container->get('file_system')->realpath($src));
    // Wait for the image to be uploaded and rendered by CKEditor 5.
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '.ck-widget.image-inline > img[src$="test-svg-upload.svg"]'));
  }

}
