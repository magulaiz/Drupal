<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\Component\Utility\Html;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;

// cspell:ignore imageresize imageupload

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Image
 * @group ckeditor5
 * @internal
 */
class ImageUrlTest extends ImageTestBase {

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

    $this->pressEditorButton('Insert image');
    $panel = $page->find('css', '.ck-dropdown__panel.ck-image-insert__panel');
    $src_input = $panel->find('css', 'input[type=text]');
    $src_input->setValue($src);
    $panel->find('xpath', "//button[span[text()='Insert']]")->click();

    $this->assertNotEmpty($assert_session->waitForElementVisible('css', $image_selector));
    $this->click($image_selector);
    $this->assertVisibleBalloon('[aria-label="Image toolbar"]');

    $this->pressEditorButton('Insert image');
    $panel = $page->find('css', '.ck-dropdown__panel.ck-image-insert__panel');
    $src_input = $panel->find('css', 'input[type=text]');
    $this->assertEquals($src, $src_input->getValue());
  }

}
