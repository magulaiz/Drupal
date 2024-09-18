<?php

declare(strict_types=1);

namespace Drupal\Tests\datetime_range\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\Tests\datetime\Functional\DateTestBase;

/**
 * Tests the daterange field rendering.
 *
 * @group datetime_range
 */
class DateRangeFormatterTest extends DateTestBase {
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
   * Tests daterange field rendering with missing end date.
   */
  public function testMissingEndDate(): void {
    $field_name = $this->fieldStorage->getName();
    $this->field->setSetting('optional_values', DateRangeItem::OPTIONAL_END)->save();
    $this->fieldStorage->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATE)->save();

    // Add new entity with start date only.
    $this->drupalGet('entity_test/add');
    $value = '2012-12-31 00:00:00';
    $start_date = new DrupalDateTime($value, timezone_open(date_default_timezone_get()));
    $date_format = DateFormat::load('html_date')->getPattern();
    $edit = [
      $field_name . '[0][value][date]' => $start_date->format($date_format),
    ];
    $this->submitForm($edit, 'Save');
    preg_match('|entity_test/manage/(\d+)|', $this->getUrl(), $match);
    $id = $match[1];
    $this->assertSession()->pageTextContains('entity_test ' . $id . ' has been created.');

    $separator = '--';
    $display_options = [
      'type' => 'daterange_default',
      'label' => 'hidden',
      'settings' => [
        'timezone_override' => '',
        'separator' => $separator,
      ],
    ];
    \Drupal::service('entity_display.repository')->getViewDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle(), 'full')
      ->setComponent($field_name, $display_options)
      ->save();

    // The rendered output will have only start date as end date is missing.
    $expected = $start_date->format(DateTimeItemInterface::DATE_STORAGE_FORMAT);
    $output = $this->renderTestEntity($id);
    $this->assertStringContainsString($expected, $output);
    $this->assertStringNotContainsString($separator, $output);
  }

}
