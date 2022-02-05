<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\editor\Entity\Editor;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\user\RoleInterface;
use Symfony\Component\Validator\ConstraintViolation;

// cspell:ignore upcasted

/**
 * Tests for CKEditor5.
 *
 * @group ckeditor5
 * @internal
 */
class CKEditor5Test extends CKEditor5TestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'media_library',
  ];

  /**
   * Tests configuring CKEditor5 for existing content.
   */
  public function testExistingContent() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Add a node with text rendered via the Plain Text format.
    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    $page->fillField('body[0][value]', '<p>This is test content</p>');
    $page->pressButton('Save');
    $assert_session->responseNotContains('<p>This is test content</p>');
    $assert_session->responseContains('&lt;p&gt;This is test content&lt;/p&gt;');

    $this->addNewTextFormat($page, $assert_session);

    // Change the node to use the new text format.
    $this->drupalGet('node/1/edit');

    // Confirm that the JavaScript that generates IE11 warnings loads.
    $assert_session->elementExists('css', 'script[src*="ckeditor5/js/ie11.user.warnings.js"]');

    $page->selectFieldOption('body[0][format]', 'ckeditor5');
    $this->assertNotEmpty($assert_session->waitForText('Change text format?'));
    $page->pressButton('Continue');
    // Ensure the editor is loaded.
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-editor'));
    $page->pressButton('Save');

    // Assert that the HTML is rendered correctly.
    $assert_session->responseContains('<p>This is test content</p>');
    $assert_session->responseNotContains('&lt;p&gt;This is test content&lt;/p&gt;');
  }

  /**
   * Ensures that attribute values are encoded.
   */
  public function testAttributeEncoding() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    FilterFormat::create([
      'format' => 'ckeditor5',
      'name' => 'CKEditor 5 with image upload',
      'roles' => [RoleInterface::AUTHENTICATED_ID],
    ])->save();
    Editor::create([
      'format' => 'ckeditor5',
      'editor' => 'ckeditor5',
      'settings' => [
        'toolbar' => [
          'items' => ['uploadImage'],
        ],
        'plugins' => [],
      ],
      'image_upload' => [
        'status' => TRUE,
        'scheme' => 'public',
        'directory' => 'inline-images',
        'max_size' => '',
      ],
    ])->save();
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('ckeditor5'),
        FilterFormat::load('ckeditor5')
      ))
    ));

    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    $this->assertNotEmpty($image_upload_field = $page->find('css', '.ck-file-dialog-button input[type="file"]'));
    $image = $this->getTestFiles('image')[0];
    $image_upload_field->attachFile($this->container->get('file_system')->realpath($image->uri));
    $assert_session->assertWaitOnAjaxRequest();

    $this->click('.ck-widget.image');
    $balloon_panel = $page->find('css', '.ck-balloon-panel');
    $balloon_buttons = $balloon_panel->findAll('css', '[aria-label="Image toolbar"] button');
    $this->assertSame('Change image text alternative', $balloon_buttons[0]->find('css', '.ck-button__label')->getHtml());
    $balloon_buttons[0]->click();
    $assert_session->waitForElementVisible('css', '.ck-balloon-panel .ck-text-alternative-form');
    $alt_override_input = $page->find('css', '.ck-balloon-panel .ck-text-alternative-form input[type=text]');
    $this->assertSame('', $alt_override_input->getValue());
    $alt_override_input->setValue('</em> Kittens & llamas are cute');
    $balloon_panel->pressButton('Save');
    $page->pressButton('Save');

    $uploaded_image = File::load(1);
    $image_uuid = $uploaded_image->uuid();
    $image_url = $this->container->get('file_url_generator')->generateString($uploaded_image->getFileUri());
    $this->drupalGet('node/1');
    $assert_session->elementExists('xpath', sprintf('//img[@alt="</em> Kittens & llamas are cute" and @data-entity-uuid="%s" and @data-entity-type="file"]', $image_uuid));

    // Drupal CKEditor 5 integrations overrides the CKEditor 5 HTML writer to
    // escape ampersand characters (&) and the angle brackets (< and >). This is
    // required because \Drupal\Component\Utility\Xss::filter fails to parse
    // element attributes with unescaped entities in value.
    // @see https://www.drupal.org/project/drupal/issues/3227831
    $this->assertEquals(sprintf('<img data-entity-uuid="%s" data-entity-type="file" src="%s" alt="&lt;/em&gt; Kittens &amp; llamas are cute">', $image_uuid, $image_url), Node::load(1)->get('body')->value);
  }

  /**
   * Ensures that CKEditor 5 integrates with file reference filter.
   */
  public function testEditorFileReferenceIntegration() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->createNewTextFormat($page, $assert_session);
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ckeditor5-toolbar-item-uploadImage'));
    $this->triggerKeyUp('.ckeditor5-toolbar-item-uploadImage', 'ArrowDown');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Image Upload');
    $page->checkField('editor[settings][plugins][ckeditor5_imageUpload][status]');
    $assert_session->assertWaitOnAjaxRequest();
    $page->checkField('filters[editor_file_reference][status]');
    $assert_session->assertWaitOnAjaxRequest();
    $this->saveNewTextFormat($page, $assert_session);

    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    $this->assertNotEmpty($image_upload_field = $page->find('css', '.ck-file-dialog-button input[type="file"]'));
    $image = $this->getTestFiles('image')[0];
    $image_upload_field->attachFile($this->container->get('file_system')->realpath($image->uri));
    // Wait until preview for the image has rendered to ensure that the image
    // upload has completed and the image has been downcast.
    // @see https://www.drupal.org/project/drupal/issues/3250587
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-content img[data-entity-uuid]'));
    $page->pressButton('Save');

    $uploaded_image = File::load(1);
    $image_url = $this->container->get('file_url_generator')->generateString($uploaded_image->getFileUri());
    $image_uuid = $uploaded_image->uuid();
    $assert_session->elementExists('xpath', sprintf('//img[@src="%s" and @loading="lazy" and @width and @height and @data-entity-uuid="%s" and @data-entity-type="file"]', $image_url, $image_uuid));

    // Ensure that width, height, and length attributes are not stored in the
    // database.
    $this->assertEquals(sprintf('<img data-entity-uuid="%s" data-entity-type="file" src="%s">', $image_uuid, $image_url), Node::load(1)->get('body')->value);

    // Ensure that data-entity-uuid and data-entity-type attributes are upcasted
    // correctly to CKEditor model.
    $this->drupalGet('node/1/edit');
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-editor'));
    $page->pressButton('Save');

    $assert_session->elementExists('xpath', sprintf('//img[@src="%s" and @loading="lazy" and @width and @height and @data-entity-uuid="%s" and @data-entity-type="file"]', $image_url, $image_uuid));
  }

  /**
   * Ensures that CKEditor italic model is converted to em.
   */
  public function testEmphasis() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Add a node with text rendered via the Plain Text format.
    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    $page->fillField('body[0][value]', '<p>This is a <em>test!</em></p>');
    $page->pressButton('Save');

    $this->createNewTextFormat($page, $assert_session);
    $this->saveNewTextFormat($page, $assert_session);

    $this->drupalGet('node/1/edit');
    $page->selectFieldOption('body[0][format]', 'ckeditor5');
    $this->assertNotEmpty($assert_session->waitForText('Change text format?'));
    $page->pressButton('Continue');

    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-editor'));
    $page->pressButton('Save');

    $assert_session->responseContains('<p>This is a <em>test!</em></p>');
  }

  /**
   * Ensures that images can have caption set.
   */
  public function testImageCaption() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Add a node with text rendered via the Plain Text format.
    $this->drupalGet('node/add');
    $page->fillField('title[0][value]', 'My test content');
    // Add image with data-caption. The foo attribute is added to be removed
    // later by CKEditor to make sure CKEditor was able to downcast data.
    $page->fillField('body[0][value]', '<img src="/sites/default/files/alpaca.jpg" data-caption="Alpacas &lt;em&gt;are&lt;/em&gt; cute" foo="bar">');
    $page->pressButton('Save');

    $this->createNewTextFormat($page, $assert_session);
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ckeditor5-toolbar-item-uploadImage'));
    $this->triggerKeyUp('.ckeditor5-toolbar-item-uploadImage', 'ArrowDown');
    $assert_session->assertWaitOnAjaxRequest();
    $page->clickLink('Image Upload');
    $assert_session->waitForText('Enable image uploads');
    $page->checkField('editor[settings][plugins][ckeditor5_imageUpload][status]');
    $assert_session->assertWaitOnAjaxRequest();
    $page->checkField('filters[filter_caption][status]');
    $assert_session->assertWaitOnAjaxRequest();
    $this->saveNewTextFormat($page, $assert_session);

    $this->drupalGet('node/1/edit');
    $page->selectFieldOption('body[0][format]', 'ckeditor5');
    $this->assertNotEmpty($assert_session->waitForText('Change text format?'));
    $page->pressButton('Continue');

    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-editor'));
    $page->pressButton('Save');

    $this->assertEquals('<img src="/sites/default/files/alpaca.jpg" data-caption="Alpacas &lt;em&gt;are&lt;/em&gt; cute">', Node::load(1)->get('body')->value);
    $assert_session->elementExists('xpath', '//figure/img[@src="/sites/default/files/alpaca.jpg" and not(@data-caption)]');
    $assert_session->responseContains('<figcaption>Alpacas <em>are</em> cute</figcaption>');
  }

}
