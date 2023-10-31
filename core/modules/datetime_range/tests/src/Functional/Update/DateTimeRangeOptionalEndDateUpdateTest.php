<?php

namespace Drupal\Tests\datetime_range\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests that optional_end_date config is added in daterange field settings.
 *
 * @group Update
 * @group legacy
 * @covers \datetime_range_post_update_add_optional_end_date
 */
class DateTimeRangeOptionalEndDateUpdateTest extends UpdatePathTestBase {
  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/datetime_range_field_config.php',

    ];
  }

  /**
   * Tests that optional_end_date config is added in daterange field settings.
   *
   * @see datetime_range_post_update_add_optional_end_date()
   */
  public function testOptionalEndDateAddedAfterUpdate() {
    $field_settings = $this->config('field.field.node.page.field_date_range')->get('settings');
    $this->assertArrayNotHasKey('optional_end_date', $field_settings);

    $this->runUpdates();

    $field_settings = $this->config('field.field.node.page.field_date_range')->get('settings');
    $this->assertArrayHasKey('optional_end_date', $field_settings);
    $this->assertFalse($field_settings['optional_end_date']);
  }

}
