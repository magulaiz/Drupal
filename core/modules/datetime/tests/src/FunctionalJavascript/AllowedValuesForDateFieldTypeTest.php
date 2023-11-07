<?php

namespace Drupal\Tests\datetime\FunctionalJavascript;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the form validation for allowed values.
 *
 * @group datetime
 */
class AllowedValuesForDateFieldTypeTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'datetime',
    'field',
    'options',
  ];

  /**
   * The used field names.
   *
   * @var string[]
   */
  protected $fieldNames;

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

    $this->fieldNames = ['field_date', 'field_list_string'];
    FieldStorageConfig::create([
      'field_name' => $this->fieldNames[0],
      'entity_type' => 'node',
      'type' => 'datetime',
      'settings' => ['datetime_type' => DateTimeItem::DATETIME_TYPE_DATETIME],
    ])->save();
    FieldStorageConfig::create([
      'field_name' => $this->fieldNames[1],
      'entity_type' => 'node',
      'type' => 'list_string',
      'settings' => [
        'allowed_values' => [
          '0' => '0',
          '1' => '1',
        ],
      ],
    ])->save();
    foreach ($this->fieldNames as $field_name) {
      FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => 'node',
        'label' => 'Test options list field',
        'bundle' => 'article',
      ])->save();
    }
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the form validation for allowed values.
   */
  public function testAllowedValuesFormValidation() {
    // Test the datetime field.
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_date');
    $page = $this->getSession()->getPage();
    $page->findField('edit-field-storage-subform-cardinality-number')->setValue('-11');
    $page->findButton('Save settings')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Limit must be higher than or equal to 1.');

    // Test the selection list field.
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_list_string');
    $page = $this->getSession()->getPage();
    $page->findField('edit-field-storage-subform-cardinality-number')->setValue('-11');
    $page->findButton('Save settings')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    // This asserts that the form is not submitted.
    $this->assertSession()->pageTextContains('The name will be used in displayed options and edit forms.');
  }

}
