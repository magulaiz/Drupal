<?php

namespace Drupal\Tests\ckeditor\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\editor\Entity\Editor;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\ckeditor\Traits\CKEditorTestTrait;

/**
 * Tests that Source mode correct responds to input changes and fires callback.
 *
 * CKEditor's source mode does not fire the change event, so we need special
 * handling to ensure the change event is handled when editing source.
 *
 * @see https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_editor.html#event-change
 *
 * @group ckeditor
 */
class SourceModeChangeEventTest extends WebDriverTestBase {

  use CKEditorTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The account.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * The FilterFormat config entity used for testing.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $filterFormat;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'ckeditor', 'filter', 'ckeditor_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a text format and associate CKEditor.
    $this->filterFormat = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
    ]);
    $this->filterFormat->save();

    Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ])->save();

    // Create a node type for testing.
    NodeType::create(['type' => 'page', 'name' => 'page'])->save();

    $field_storage = FieldStorageConfig::loadByName('node', 'body');

    // Create a body field instance for the 'page' node type.
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => 'Body',
      'settings' => ['display_summary' => TRUE],
      'required' => TRUE,
    ])->save();

    // Assign widget settings for the 'default' form mode.
    EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent('body', ['type' => 'text_textarea_with_summary'])
      ->save();

    $this->account = $this->drupalCreateUser([
      'administer nodes',
      'create page content',
      'use text format filtered_html',
    ]);
    $this->drupalLogin($this->account);
  }

  /**
   * Tests if changes in source mode correctly trigger the change event.
   */
  public function testSourceMode() {
    $this->drupalGet('node/add/page');
    $this->waitForEditor();

    // Check that the editor value hasn't changed.
    $this->assertSession()->elementNotExists('css', '#edit-body-0-value[data-editor-value-is-changed="true"]');

    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->pressEditorButton('source');
    $textarea = $this->assertSession()->waitForElementVisible('css', '.cke_source');
    $this->assertNotEmpty($textarea);
    $textarea->setValue('some arbitrary text');
    $this->getSession()->switchToWindow();
    $this->assertSession()->waitForElementVisible('css', '#edit-body-0-value[data-editor-value-is-changed="true"]');

    // Check that the editor value has been flagged as changed.
    $this->assertSession()->elementExists('css', '#edit-body-0-value[data-editor-value-is-changed="true"]');

  }

}
