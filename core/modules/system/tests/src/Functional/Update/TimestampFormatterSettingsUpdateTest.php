<?php

namespace Drupal\Tests\system\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use PHPUnit\Framework\Assert;

/**
 * Tests the update of timestamp formatter settings.
 *
 * @group system
 * @group legacy
 */
class TimestampFormatterSettingsUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.3.0.bare.standard.php.gz',
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.timestamp-formatter-settings-2921810.php',
    ];
  }

  /**
   * Tests the update of timestamp formatter settings.
   *
   * @see system_post_update_timestamp_formatter()
   * @see views_post_update_timestamp_formatter()
   */
  public function testPostUpdateTimestampFormatter(): void {
    $config_factory = \Drupal::configFactory();

    $test_cases = [
      // Timestamp formatter in entity view displays.
      'core.entity_view_display.node.page.default' => 'content.field_foo.settings',
      // Timestamp formatter in views.
      'views.view.content' => 'display.default.display_options.fields.changed.settings',
    ];

    foreach ($test_cases as $config_name => $config_trail) {
      // Check that 'tooltip' and 'time_diff' are missing before update.
      $settings = $config_factory->get($config_name)->get($config_trail);
      Assert::assertArrayNotHasKey('tooltip', $settings);
      Assert::assertArrayNotHasKey('time_diff', $settings);
    }

    $this->runUpdates();

    foreach ($test_cases as $config_name => $config_trail) {
      // Check that 'tooltip' and 'time_diff' were created after update.
      $settings = $config_factory->get($config_name)->get($config_trail);
      Assert::assertArrayHasKey('tooltip', $settings);
      // Check that 'tooltip' is disabled for existing formatters.
      Assert::assertSame([
        'date_format' => '',
        'custom_date_format' => '',
      ], $settings['tooltip']);
      Assert::assertArrayHasKey('time_diff', $settings);
    }
  }

}
