<?php

namespace Drupal\Tests\datetime\Functional;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the form validation for allowed values.
 *
 * @group datetime
 */
class AllowedValuesForDateFieldTypeTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'datetime',
    'field',
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
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => 'field_date',
      'entity_type' => 'node',
      'type' => 'datetime',
      'settings' => ['datetime_type' => DateTimeItem::DATETIME_TYPE_DATETIME],
    ]);
    $fieldStorage->save();
    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => 'article',
      'required' => TRUE,
    ]);
    $field->save();
    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the form validation for allowed values.
   */
  public function testAllowedValuesFormValidation() {
    $this->drupalGet('/admin/structure/types/manage/article/fields/node.article.field_date');
    $edit = [
      'label' => 'test',
      'field_storage[subform][cardinality_number]' => -1,
    ];
    $this->submitForm($edit, 'Save settings');

    $this->assertSession()->pageTextContains('Limit must be higher than or equal to 1.');
  }

}
