<?php

declare(strict_types=1);

namespace Drupal\Tests\datetime_range\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\datetime\Functional\DateTestBase;

/**
 * Tests Daterange widgets functionality.
 *
 * @group datetime_range
 */
class DateRangeWidgetTest extends DateTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['datetime_range'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function getTestFieldType(): string {
    return 'daterange';
  }

  /**
   * Tests daterange field with missing end date.
   */
  public function testMissingEndDate(): void {
    // Create a test content type.
    $this->drupalCreateContentType(['type' => 'daterange_content']);

    $field_name = $this->randomMachineName();
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'daterange',
      'settings' => ['datetime_type' => DateTimeItem::DATETIME_TYPE_DATE],
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'field_name' => $field_name,
      'bundle' => 'daterange_content',
      'required' => TRUE,
    ]);
    $field->save();

    $field->setSetting('optional_values', DateRangeItem::OPTIONAL_END)->save();

    \Drupal::service('entity_display.repository')
      ->getFormDisplay('node', 'daterange_content')
      ->setComponent($field_name, [
        'type' => 'datetime_default',
      ])
      ->save();

    // Add node with start date only.
    $this->drupalGet('node/add/daterange_content');
    $value = '2012-12-31 00:00:00';
    $start_date = new DrupalDateTime($value, timezone_open(date_default_timezone_get()));
    $date_format = DateFormat::load('html_date')->getPattern();
    $title = $this->randomString();
    $edit = [
      'title[0][value]' => $title,
      'body[0][value]' => $this->randomString(),
      $field_name . '[0][value][date]' => $start_date->format($date_format),
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('daterange_content ' . $title . ' has been created.');
    $node = $this->drupalGetNodeByTitle($title);

    // Assert correct start date and end date is stored.
    $this->assertEquals($start_date->format($date_format), $node->get($field_name)->offsetGet(0)->value);
    $this->assertEquals(NULL, $node->get($field_name)->offsetGet(0)->end_value);
  }

}
