<?php

declare(strict_types=1);

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Symfony\Component\Validator\ConstraintViolation;

// cspell:ignore imageresize

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Image
 * @group ckeditor5
 * @group #slow
 * @internal
 */
class ImageUrlTest extends ImageTestBase {

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
            'allowed_html' => '<p> <br> <em> <a href> <img alt height width src data-caption data-align>',
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
   * Tests the Drupal image URL widget.
   */
  public function testImageUrlWidget(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $image_selector = '.ck-widget.image-inline';
    $src = $this->imageAttributes()['src'];

    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();

    // Store the $src value in the revision textarea so we can copy and paste it
    // as the modal which pops up in CKEditor can't be manipulated via webdriver.
    $revision_textarea = $page->find('css', 'textarea[data-drupal-selector="edit-revision-log-0-value"]');
    $revision_value = $revision_textarea->getValue();
    $revision_textarea->setValue($src);
    $revision_textarea->click();
    $control_key = "\xEE\x80\x89";
    $actions = [
      'actions' => [
        [
          'type' => 'key',
          'id' => 'keyboard1',
          'actions' => [
            ['type' => 'keyDown', 'value' => $control_key],
            ['type' => 'keyDown', 'value' => 'a'],
            ['type' => 'keyUp', 'value' => $control_key],
            ['type' => 'keyUp', 'value' => 'a'],
            ['type' => 'keyDown', 'value' => $control_key],
            ['type' => 'keyDown', 'value' => 'c'],
            ['type' => 'keyUp', 'value' => $control_key],
            ['type' => 'keyUp', 'value' => 'c'],
          ],
        ],
      ],
    ];
    $this->getSession()->getDriver()->getWebDriverSession()->postActions($actions);
    $revision_textarea->setValue($revision_value);

    $this->pressEditorButton('Insert image via URL');
    $panel = $page->find('css', '.ck-dropdown__panel .ck-image-insert-url');
    $actions = [
      'actions' => [
        [
          'type' => 'key',
          'id' => 'keyboard1',
          'actions' => [
            ['type' => 'keyDown', 'value' => $control_key],
            ['type' => 'keyDown', 'value' => 'v'],
            ['type' => 'keyUp', 'value' => $control_key],
            ['type' => 'keyUp', 'value' => 'v'],
          ],
        ],
      ],
    ];
    $this->getSession()->getDriver()->getWebDriverSession()->postActions($actions);
    $panel->find('xpath', "//button[span[text()='Insert']]")->click();

    $this->assertNotEmpty($assert_session->waitForElementVisible('css', $image_selector));
    $this->click($image_selector);
    $this->assertVisibleBalloon('[aria-label="Image toolbar"]');

    $this->pressEditorButton('Update image URL');
    $panel = $page->find('css', '.ck-dropdown__panel .ck-image-insert-url');
    $src_input = $panel->find('css', 'input[type=text]');
    $this->assertEquals($src, $src_input->getValue());
  }

}
