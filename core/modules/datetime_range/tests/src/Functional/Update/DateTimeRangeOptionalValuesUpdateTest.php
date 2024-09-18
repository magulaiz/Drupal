<?php

declare(strict_types=1);

namespace Drupal\Tests\datetime_range\Functional\Update;

use Drupal\datetime_range\Plugin\Field\FieldType\DateRangeItem;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests that optional_values config is added in daterange field settings.
 *
 * @group Update
 * @group legacy
 * @covers \datetime_range_post_update_add_optional_values
 */
class DateTimeRangeOptionalValuesUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.filled.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/datetime_range_field_config.php',

    ];
  }

  /**
   * Tests that optional_values config is added in daterange field settings.
   *
   * @see datetime_range_post_update_add_optional_values()
   */
  public function testOptionalValuesAddedAfterUpdate(): void {
    $field_settings = $this->config('field.field.node.page.field_date_range')->get('settings');
    $this->assertArrayNotHasKey('optional_values', $field_settings);

    $this->runUpdates();

    $field_settings = $this->config('field.field.node.page.field_date_range')->get('settings');
    $this->assertArrayHasKey('optional_values', $field_settings);
    $this->assertSame(DateRangeItem::OPTIONAL_NONE, $field_settings['optional_values']);
  }

}
