<?php

namespace Drupal\Tests\datetime\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\node\Entity\Node;

/**
 * Tests that settings are properly updated during database updates.
 *
 * @group datetime
 * @group legacy
 */
class DatetimeUpdateTest extends UpdatePathTestBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->configFactory = $this->container->get('config.factory');
    $this->entityFieldManager = $this->container->get('entity_field.manager');
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8.bare.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/datetime-date_8001-values.php',
    ];
  }

  /**
   * Tests that time zone changes are applied.
   *
   * @see datetime_update_8001()
   */
  public function testTimezoneSettings() {
    // Load the 'node.field_date_1' field storage config, and check that the is
    // no time zone storage yet.
    $config = $this->configFactory->get('field.storage.node.field_date_1');
    $settings = $config->get('settings');
    $this->assertFalse(array_key_exists('timezone_storage', $settings));

    // Run updates.
    $this->runUpdates();

    // Check the time zone storage has been added and defaulted to FALSE.
    $config = $this->configFactory->get('field.storage.node.field_date_1');
    $settings = $config->get('settings');
    $this->assertTrue(array_key_exists('timezone_storage', $settings));
    $this->assertFalse($settings['timezone_storage']);

    // Ensure time zone column added and NULL for existing content.
    $node = Node::load(1);
    $this->assertNull($node->field_date_1->timezone);
  }

}
