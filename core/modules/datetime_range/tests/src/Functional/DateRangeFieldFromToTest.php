<?php

declare(strict_types=1);

namespace Drupal\Tests\datetime_range\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\datetime_range\DateTimeRangeConstantsInterface;
use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\Tests\datetime\Functional\DateTestBase;

/**
 * Tests Daterange field functionality.
 *
 * @group datetime
 * @group #slow
 */
class DateRangeFieldFromToTest extends DateTestBase {

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
   * The default display settings to use for the formatters.
   *
   * @var array
   */
  protected $defaultSettings = [
    'timezone_override' => '',
    'separator' => '-',
    'from_to' => DateTimeRangeConstantsInterface::BOTH,
  ];

  /**
   * {@inheritdoc}
   */
  protected function getTestFieldType() {
    return 'daterange';
  }

  /**
   * Tests displaying dates with the 'from_to' setting.
   *
   * @dataProvider fromToSettingDataProvider
   */
  public function testFromToSetting(array $expected, string $datetime_type, string $field_formatter_type, array $display_settings = []): void {
    $field_name = $this->fieldStorage->getName();

    // Create a test content type.
    $this->drupalCreateContentType(['type' => 'date_content']);

    // Ensure the field to a datetime field.
    $this->fieldStorage->setSetting('datetime_type', $datetime_type);
    $this->fieldStorage->save();

    // Build up dates in the UTC timezone.
    $value = '2012-12-31 00:00:00';
    $start_date = new DrupalDateTime($value, 'UTC');
    $end_value = '2013-06-06 00:00:00';
    $end_date = new DrupalDateTime($end_value, 'UTC');

    // Submit a valid date and ensure it is accepted.
    $date_format = DateFormat::load('html_date')->getPattern();

    $edit = [
      "{$field_name}[0][value][date]" => $start_date->format($date_format),
      "{$field_name}[0][end_value][date]" => $end_date->format($date_format),
    ];

    // Supply time as well when field is a datetime field.
    if ($datetime_type === DateRangeItem::DATETIME_TYPE_DATETIME) {
      $time_format = DateFormat::load('html_time')->getPattern();
      $edit["{$field_name}[0][value][time]"] = $start_date->format($time_format);
      $edit["{$field_name}[0][end_value][time]"] = $end_date->format($time_format);
    }

    $this->drupalGet('entity_test/add');
    $this->submitForm($edit, t('Save'));
    preg_match('|entity_test/manage/(\d+)|', $this->getUrl(), $match);
    $id = $match[1];
    $this->assertSession()->pageTextContains(t('entity_test @id has been created.', ['@id' => $id]));

    // Now set display options.
    $this->displayOptions = [
      'type' => $field_formatter_type,
      'label' => 'hidden',
      'settings' => $display_settings + [
        'format_type' => 'short',
        'separator' => 'THE_SEPARATOR',
      ] + $this->defaultSettings,
    ];

    \Drupal::service('entity_display.repository')->getViewDisplay(
      $this->field->getTargetEntityTypeId(),
      $this->field->getTargetBundle(),
      'full')
      ->setComponent($field_name, $this->displayOptions)
      ->save();

    $output = $this->renderTestEntity($id);
    foreach ($expected as $content => $is_expected) {
      if ($is_expected) {
        $this->assertStringContainsString($content, $output);
      }
      else {
        $this->assertStringNotContainsString($content, $output);
      }
    }
  }

  /**
   * The data provider for testing the 'from_to' setting.
   *
   * @return array
   *   An array of date settings to test the behavior of the 'from_to' setting.
   */
  public static function fromToSettingDataProvider(): array {
    $datetime_types = [
      DateRangeItem::DATETIME_TYPE_DATE => [
        'daterange_default' => [
          DateTimeRangeConstantsInterface::START_DATE => '12/31/2012',
          DateTimeRangeConstantsInterface::END_DATE => '06/06/2013',
        ],
        'daterange_plain' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06',
        ],
        'daterange_custom' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06',
        ],
      ],
      DateRangeItem::DATETIME_TYPE_DATETIME => [
        'daterange_default' => [
          DateTimeRangeConstantsInterface::START_DATE => '12/31/2012 - 00:00',
          DateTimeRangeConstantsInterface::END_DATE => '06/06/2013 - 00:00',
        ],
        'daterange_plain' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31T00:00:00',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06T00:00:00',
        ],
        'daterange_custom' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31T00:00:00',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06T00:00:00',
        ],
      ],
      DateRangeItem::DATETIME_TYPE_ALLDAY => [
        'daterange_default' => [
          DateTimeRangeConstantsInterface::START_DATE => '12/31/2012',
          DateTimeRangeConstantsInterface::END_DATE => '06/06/2013',
        ],
        'daterange_plain' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06',
        ],
        'daterange_custom' => [
          DateTimeRangeConstantsInterface::START_DATE => '2012-12-31',
          DateTimeRangeConstantsInterface::END_DATE => '2013-06-06',
        ],
      ],
    ];

    $return = [];
    $separator = ' THE_SEPARATOR ';
    foreach ($datetime_types as $datetime_type => $field_formatters) {
      foreach ($field_formatters as $field_formatter_type => $dates) {
        // Both start and end date.
        $return[$datetime_type . '-' . $field_formatter_type . '-both'] = [
          'expected' => [
            $dates[DateTimeRangeConstantsInterface::START_DATE] => TRUE,
            $separator => TRUE,
            $dates[DateTimeRangeConstantsInterface::END_DATE] => TRUE,
          ],
          'datetime_type' => $datetime_type,
          'field_formatter_type' => $field_formatter_type,
        ];

        // Only start date.
        $return[$datetime_type . '-' . $field_formatter_type . '-start_date'] = [
          'expected' => [
            $dates[DateTimeRangeConstantsInterface::START_DATE] => TRUE,
            $separator => FALSE,
            $dates[DateTimeRangeConstantsInterface::END_DATE] => FALSE,
          ],
          'datetime_type' => $datetime_type,
          'field_formatter_type' => $field_formatter_type,
          ['from_to' => DateTimeRangeConstantsInterface::START_DATE],
        ];

        // Only end date.
        $return[$datetime_type . '-' . $field_formatter_type . '-end_date'] = [
          'expected' => [
            $dates[DateTimeRangeConstantsInterface::START_DATE] => FALSE,
            $separator => FALSE,
            $dates[DateTimeRangeConstantsInterface::END_DATE] => TRUE,
          ],
          'datetime_type' => $datetime_type,
          'field_formatter_type' => $field_formatter_type,
          ['from_to' => DateTimeRangeConstantsInterface::END_DATE],
        ];
      }
    }

    return $return;
  }

}
