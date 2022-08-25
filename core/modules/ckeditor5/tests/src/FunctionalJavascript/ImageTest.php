<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\editor\Entity\Editor;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Symfony\Component\Validator\ConstraintViolation;

// cspell:ignore imageresize imageupload

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Image
 * @group ckeditor5
 * @internal
 */
class ImageTest extends ImageTestBase {

  /**
   * The sample image File entity to embed.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

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
            'allowed_html' => '<p> <br> <em> <a href> <img src alt data-entity-uuid data-entity-type height width data-caption data-align>',
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
            'drupalInsertImage',
            'sourceEditing',
            'link',
            'italic',
          ],
        ],
        'plugins' => [
          'ckeditor5_sourceEditing' => [
            'allowed_tags' => [],
          ],
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

    // Create a sample host entity to embed images in.
    $this->file = File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
    ]);
    $this->file->save();
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
   * Provides the relevant image attributes.
   *
   * @return string[]
   */
  protected function imageAttributes() {
    return [
      'data-entity-type' => 'file',
      'data-entity-uuid' => $this->file->uuid(),
      'src' => $this->file->createFileUrl(),
    ];
  }

  /**
   * Tests that alt text is required for images.
   *
   * @see https://ckeditor.com/docs/ckeditor5/latest/framework/guides/architecture/editing-engine.html#conversion
   *
   * @dataProvider providerAltTextRequired
   */
  public function testAltTextRequired(bool $unrestricted) {
    // Disable filter_html.
    if ($unrestricted) {
      FilterFormat::load('test_format')
        ->setFilterConfig('filter_html', ['status' => FALSE])
        ->save();
    }

    // Make the test content has a block image and an inline image.
    $img_tag = '<img ' . $this->imageAttributesAsString() . ' width="500" />';
    $this->host->body->value .= $img_tag . "<p>$img_tag</p>";
    $this->host->save();

    $page = $this->getSession()->getPage();

    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $assert_session = $this->assertSession();

    // Confirm both of the images exist.
    $this->assertNotEmpty($image_block = $assert_session->waitForElementVisible('css', ".ck-content .ck-widget.image"));
    $this->assertNotEmpty($image_inline = $assert_session->waitForElementVisible('css', ".ck-content .ck-widget.image-inline"));

    // Confirm both of the images have an alt text required warning.
    $this->assertNotEmpty($image_block->find('css', '.image-alternative-text-missing-wrapper'));
    $this->assertNotEmpty($image_inline->find('css', '.image-alternative-text-missing-wrapper'));

    // Add alt text to the block image.
    $image_block->find('css', '.image-alternative-text-missing button')->click();
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '.ck-balloon-panel'));
    $this->assertVisibleBalloon('.ck-text-alternative-form');

    // Ensure that the missing alt text warning is hidden when the alternative
    // text form is open.
    $assert_session->waitForElement('css', '.ck-content .ck-widget.image .image-alternative-text-missing.ck-hidden');
    $assert_session->elementExists('css', '.ck-content .ck-widget.image-inline .image-alternative-text-missing');
    $assert_session->elementNotExists('css', '.ck-content .ck-widget.image-inline .image-alternative-text-missing.ck-hidden');

    // Ensure that the missing alt text error is not added to decorative images.
    $this->assertNotEmpty($decorative_button = $this->getBalloonButton('Decorative image'));
    $assert_session->elementExists('css', '.ck-balloon-panel .ck-text-alternative-form input[type=text]');
    $decorative_button->click();
    $assert_session->elementExists('css', '.ck-content .ck-widget.image .image-alternative-text-missing.ck-hidden');
    $assert_session->elementExists('css', ".ck-content .ck-widget.image-inline .image-alternative-text-missing-wrapper");
    $assert_session->elementNotExists('css', '.ck-content .ck-widget.image-inline .image-alternative-text-missing.ck-hidden');

    // Ensure that the missing alt text error is removed after saving the
    // changes.
    $this->assertNotEmpty($save_button = $this->getBalloonButton('Save'));
    $save_button->click();
    $this->assertTrue($assert_session->waitForElementRemoved('css', ".ck-content .ck-widget.image .image-alternative-text-missing-wrapper"));
    $assert_session->elementExists('css', '.ck-content .ck-widget.image-inline .image-alternative-text-missing-wrapper');

    // Ensure that the decorative image downcasts into empty alt attribute.
    $editor_dom = $this->getEditorDataAsDom();
    $decorative_img = $editor_dom->getElementsByTagName('img')->item(0);
    $this->assertTrue($decorative_img->hasAttribute('alt'));
    $this->assertEmpty($decorative_img->getAttribute('alt'));

    // Ensure that missing alt text error is not added to images with alt text.
    $this->assertNotEmpty($alt_text_button = $this->getBalloonButton('Change image alternative text'));
    $alt_text_button->click();

    $decorative_button->click();
    $this->assertNotEmpty($save_button = $this->getBalloonButton('Save'));
    $this->assertTrue($save_button->hasClass('ck-disabled'));

    $this->assertNotEmpty($alt_override_input = $page->find('css', '.ck-balloon-panel .ck-text-alternative-form input[type=text]'));
    $alt_override_input->setValue('There is now alt text');
    $this->assertTrue($assert_session->waitForElementRemoved('css', '.ck-balloon-panel .ck-text-alternative-form .ck-disabled'));
    $this->assertFalse($save_button->hasClass('ck-disabled'));
    $save_button->click();

    // Save the node and confirm that the alt text is retained.
    $page->pressButton('Save');
    $this->assertNotEmpty($assert_session->waitForElement('css', 'img[alt="There is now alt text"]'));

    // Ensure that alt form is opened after image upload.
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assertNotEmpty($image_upload_field = $page->find('css', '.ck-file-dialog-button input[type="file"]'));
    $image = $this->getTestFiles('image')[0];
    $image_upload_field->attachFile($this->container->get('file_system')->realpath($image->uri));
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '.ck-widget.image'));
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '.ck-balloon-panel'));
    $this->assertVisibleBalloon('.ck-text-alternative-form');
  }

  /**
   * Tests the image resize plugin.
   *
   * Confirms that enabling the resize plugin introduces the resize class to
   * images within CKEditor 5.
   *
   * @param bool $is_resize_enabled
   *   Boolean flag to test enabled or disabled.
   *
   * @dataProvider providerResize
   */
  public function testResize(bool $is_resize_enabled): void {
    // Disable resize plugin because it is enabled by default.
    if (!$is_resize_enabled) {
      Editor::load('test_format')->setSettings([
        'toolbar' => [
          'items' => [
            'drupalInsertImage',
          ],
        ],
        'plugins' => [
          'ckeditor5_imageResize' => [
            'allow_resize' => FALSE,
          ],
        ],
      ])->save();
    }

    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    $this->assertNotEmpty($image_upload_field = $page->find('css', '.ck-file-dialog-button input[type="file"]'));
    $image = $this->getTestFiles('image')[0];
    $image_upload_field->attachFile($this->container->get('file_system')->realpath($image->uri));
    $image_figure = $assert_session->waitForElementVisible('css', 'figure');
    $this->assertSame($is_resize_enabled, $image_figure->hasClass('ck-widget_with-resizer'));
  }

  /**
   * Tests the ckeditor5_imageResize and ckeditor5_imageUpload settings forms.
   */
  public function testImageSettingsForm() {
    $assert_session = $this->assertSession();

    $this->drupalGet('admin/config/content/formats/manage/test_format');

    // The image resize and upload plugin settings forms should be present.
    $assert_session->elementExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-imageresize"]');
    $assert_session->elementExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-image"]');

    // Removing the drupalImageInsert button from the toolbar must remove the
    // plugin settings forms too.
    $this->triggerKeyUp('.ckeditor5-toolbar-item-drupalInsertImage', 'ArrowUp');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementNotExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-imageresize"]');
    $assert_session->elementNotExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-image"]');

    // Re-adding the drupalImageInsert button to the toolbar must re-add the
    // plugin settings forms too.
    $this->triggerKeyUp('.ckeditor5-toolbar-item-drupalInsertImage', 'ArrowDown');
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-imageresize"]');
    $assert_session->elementExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-image"]');
  }

}
