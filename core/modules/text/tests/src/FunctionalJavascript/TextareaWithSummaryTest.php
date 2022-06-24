<?php

namespace Drupal\Tests\text\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests the JavaScript functionality of the text_textarea_with_summary widget.
 *
 * @group text
 */
class TextareaWithSummaryTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['text', 'node', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);

    $this->drupalCreateContentType(['type' => 'page']);

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
  }

  /**
   * Helper to test toggling the summary area.
   */
  protected function assertSummaryToggle() {
    $this->drupalGet('node/add/page');
    $widget = $this->getSession()->getPage()->findById('edit-body-wrapper');
    $summary_field = $widget->findField('edit-body-0-summary');

    $this->assertEquals(FALSE, $summary_field->isVisible(), 'Summary field is hidden by default.');
    $this->assertEquals(FALSE, $widget->hasButton('Hide summary'), 'No Hide summary link by default.');

    $widget->pressButton('Edit summary');
    $this->assertEquals(FALSE, $widget->hasButton('Edit summary'), 'Edit summary link is removed after clicking.');
    $this->assertEquals(TRUE, $summary_field->isVisible(), 'Summary field is shown.');

    $widget->pressButton('Hide summary');
    $this->assertEquals(FALSE, $widget->hasButton('Hide summary'), 'Hide summary link is removed after clicking.');
    $this->assertEquals(FALSE, $summary_field->isVisible(), 'Summary field is hidden again.');
    $this->assertEquals(TRUE, $widget->hasButton('Edit summary'), 'Edit summary link is visible again.');
  }

  /**
   * Tests the textSummary javascript behavior.
   */
  public function testTextSummaryBehavior() {
    // Test with field defaults.
    $this->assertSummaryToggle();

    // Repeat test with non-empty field description.
    $body_field = FieldConfig::loadByName('node', 'page', 'body');
    $body_field->set('description', 'Text with Summary field description.');
    $body_field->save();

    $this->assertSummaryToggle();

    // Test summary is shown when non-empty.
    $node = $this->createNode([
      'body' => [
        [
          'value' => $this->randomMachineName(32),
          'summary' => $this->randomMachineName(32),
          'format' => filter_default_format(),
        ],
      ],
    ]);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $page = $this->getSession()->getPage();
    $summary_field = $page->findField('edit-body-0-summary');

    $this->assertEquals(TRUE, $summary_field->isVisible(), 'Non-empty summary field is shown by default.');
  }

  /**
   * Tests the textSummary javascript behavior with unlimited values config and show summary flag on/off.
   */
  public function testCardinalitySettings() {

    $edit = [
      'new_storage_type' => 'text_with_summary',
      'label' => 'Text with summary',
      'field_name' => 'text_with_summary',
    ];

    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $this->drupalGet('admin/structure/types/manage/article/fields/add-field');
    $page->findField('new_storage_type')->setValue('text_with_summary');
    $assert_session->waitForField('label')->setValue('Text with summary');
    $machine_name = $assert_session->waitForElement('xpath', '//*[@id="edit-label-machine-name-suffix"]/span[contains(text(), "field_text_with_summary")]');
    $this->assertNotEmpty($machine_name);
    $page->pressButton('Save and continue');

    $edit = [
      'cardinality' => 'number',
      'cardinality_number' => '-1',
    ];
    $machine_name = $assert_session->waitForElement('xpath', '//*[@id="form-item-field-text-with-summary-0-value"]/button[contains(text(), "field_text_with_summary")]');
    $this->submitForm($edit, 'Save field settings');

    // Setting summary field visibility to visible.
    $field_edit_settings = 'admin/structure/types/manage/article/fields/node.article.field_text_with_summary';
    $this->drupalGet($field_edit_settings);
    $assert_session->waitForField('label')->setValue('Text with summary');
    $assert_session->waitForField('settings[display_summary]')->setValue(1);
    $page->pressButton('Save settings');

    $edit = [
      'cardinality' => 'number',
      'cardinality_number' => '',
    ];

    $field_edit_settings = 'admin/structure/types/manage/article/fields/node.article.field_text_with_summary/storage';
    $this->drupalGet($field_edit_settings);
    $this->submitForm($edit, 'Save field settings');

    // Test summary visibility.
    $this->drupalGet('node/add/article');
    $summary = $page->findAll('css', '.form-item-field-text-with-summary-0-value button');
    $this->assertCount(1, $summary);
    $this->assertStringContainsStringIgnoringCase($this->t('Edit summary'), $summary[0]->getText());

    // Setting summary visibility config to hidden.
    $field_edit_settings = 'admin/structure/types/manage/article/fields/node.article.field_text_with_summary';
    $this->drupalGet($field_edit_settings);
    $assert_session->waitForField('label')->setValue('Text with summary');
    $assert_session->waitForField('settings[display_summary]')->setValue(0);
    $page->pressButton('Save settings');

    // Testing summary visibility.
    $this->drupalGet('node/add/article');
    $summary = $page->findAll('css', '.form-item-field-text-with-summary-0-value button');
    $this->assertCount(0, $summary);
  }

}
