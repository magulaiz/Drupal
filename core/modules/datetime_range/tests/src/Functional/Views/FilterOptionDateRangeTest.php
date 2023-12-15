<?php

namespace Drupal\Tests\datetime\Functional\Views;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\views\Tests\ViewTestData;

/**
 * Tests Views filters for datetime fields.
 *
 * @group datetime
 */
class FilterOptionDateRangeTest extends BrowserTestBase {

  /**
   * Name of the field.
   *
   * Note, this is used in the default test view.
   *
   * @var string
   */
  protected $fieldName = 'field_date_range';

  /**
   * {@inheritdoc}
   */
  public static $testViews = ['test_filter_option_datetime'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Nodes to test.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes = [];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime_range_test',
    'node',
    'views',
    'views_ui',
    'datetime_range',
  ];

  /**
   * {@inheritdoc}
   *
   * Create nodes with relative dates of yesterday, today, and tomorrow.
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp();

    $now = \Drupal::time()->getRequestTime();

    $admin_user = $this->drupalCreateUser(['administer views'], 'vAdmin', TRUE);
    $this->drupalLogin($admin_user);

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Add a date field to page nodes.
    $fieldStorage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'type' => 'daterange',
    ]);
    $fieldStorage->save();
    $field = FieldConfig::create([
      'field_storage' => $fieldStorage,
      'bundle' => 'page',
      'required' => TRUE,
    ]);
    $field->save();

    // Create some nodes.
    $dates1 = [
      // Tomorrow.
      DrupalDateTime::createFromTimestamp($now + 86400, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      // Today.
      DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      // Yesterday.
      DrupalDateTime::createFromTimestamp($now - 86400, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ];

    $dates2 = [
      // Tomorrow.
      DrupalDateTime::createFromTimestamp($now + (2 * 86400), DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      // Today.
      DrupalDateTime::createFromTimestamp($now + (3 * 86400), DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      // Yesterday.
      DrupalDateTime::createFromTimestamp($now + (4 * 86400), DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ];

    $this->nodes = [];
    foreach ($dates1 + $dates2 as $date) {
      $this->nodes[] = $this->drupalCreateNode([
        $this->fieldName => [
          'value' => $date,
          'end_value' => $dates2[0],
        ],
      ]);

      $this->nodes[] = $this->drupalCreateNode([
        $this->fieldName => [
          'value' => $date,
          'end_value' => $dates2[1],
        ],
      ]);

      $this->nodes[] = $this->drupalCreateNode([
        $this->fieldName => [
          'value' => $date,
          'end_value' => $dates2[2],
        ],
      ]);
    }
    // Add a node where the date field is empty.
    $this->nodes[] = $this->drupalCreateNode();

    // Views needs to be aware of the new field.
    $this->container->get('views.views_data')->clear();

    // Load test views.
    ViewTestData::createTestViews(static::class, ['datetime_range_test']);
  }

  /**
   * Test date filter with date-time range filter options.
   */
  public function testVariousDateTimeRangeOptions() {
    $now = \Drupal::time()->getRequestTime();

    $edit = [];
    $edit['options[operator]'] = 'starts_on';
    $edit['options[value][value][date]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][value][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(3, $results);

    $edit = [];
    $edit['options[operator]'] = 'starts_before';
    $edit['options[value][value][date]'] = DrupalDateTime::createFromTimestamp($now + 86400, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][value][time]'] = DrupalDateTime::createFromTimestamp($now + 86400, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(6, $results);

    $edit = [];
    $edit['options[operator]'] = 'starts_on_before';
    $edit['options[value][value][date]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][value][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(6, $results);

    $edit = [];
    $edit['options[operator]'] = 'starts_after';
    $edit['options[value][value][date]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][value][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(3, $results);

    $edit = [];
    $edit['options[operator]'] = 'starts_on_after';
    $edit['options[value][value][date]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][value][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(6, $results);

    $edit = [];
    $edit['options[operator]'] = 'starts_between';
    $edit['options[value][start][date]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][start][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);
    $edit['options[value][end][date]'] = DrupalDateTime::createFromTimestamp($now + (2 * 86400), DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $edit['options[value][end][time]'] = DrupalDateTime::createFromTimestamp($now, DateTimeItemInterface::STORAGE_TIMEZONE)->format(DateTimeItemInterface::TIME_STORAGE_FORMAT);

    $this->drupalGet('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName);
    $this->drupalPostForm('admin/structure/views/nojs/handler/test_filter_option_datetime/default/filter/' . $this->fieldName, $edit, 'Apply');
    $this->drupalPostForm('admin/structure/views/view/test_filter_option_datetime/edit/default', [], 'Save');
    $this->getSession()->getPage()->pressButton('Update preview');
    $results = $this->cssSelect('.view-content .field-content');
    $this->assertCount(6, $results);
  }

}
