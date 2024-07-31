<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\views\Entity\View;

/**
 * Tests the upgrade path for adding description for timestamp formatter settings.
 *
 * @group Update
 *
 * @see views_post_update_timestamp_formatter_time_diff()
 */
class TimestampFormatterUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test'];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-10.3.0.filled.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/timestamp-formatter.php',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installModulesFromClassProperty($this->container);
  }

  /**
   * Tests that timestamp formatter settings was updated properly.
   */
  public function testViewsFieldPluginConversion(): void {
    $view = View::load('test_timestamp_formatter_update');
    $data = $view->toArray();
    $this->assertArrayNotHasKey('description', $data['display']['default']['display_options']['fields']['created']['settings']['time_diff']);

    $this->runUpdates();

    $view = View::load('test_timestamp_formatter_update');
    $data = $view->toArray();
    $this->assertArrayHasKey('description', $data['display']['default']['display_options']['fields']['created']['settings']['time_diff']);

  }

}
