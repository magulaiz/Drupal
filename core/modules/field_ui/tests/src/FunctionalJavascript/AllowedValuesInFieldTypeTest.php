<?php

namespace Drupal\Tests\field_ui\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the form validation for allowed values.
 *
 * @group field_ui
 */
class AllowedValuesInFieldTypeTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'field',
    'options',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    parent::setUp();
    $this->drupalCreateContentType([
      'name' => 'Article',
      'type' => 'article',
    ]);

    FieldStorageConfig::create([
      'field_name' => 'field_text',
      'entity_type' => 'node',
      'type' => 'text',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_text',
      'entity_type' => 'node',
      'bundle' => 'article',
    ])->save();
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the form validation for allowed values.
   */
  public function testAllowedValuesFormValidation() {
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_text');
    $page = $this->getSession()->getPage();
    $page->findField('edit-field-storage-subform-cardinality-number')->setValue('-11');
    $page->findButton('Save settings')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Limit must be higher than or equal to 1.');

  }

}
