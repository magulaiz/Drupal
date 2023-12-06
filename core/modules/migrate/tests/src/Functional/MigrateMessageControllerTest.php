<?php

namespace Drupal\Tests\migrate\Functional;

use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the MigrateController class.
 *
 * @group migrate
 */
class MigrateMessageControllerTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'message_test',
    'migrate',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Migration IDs.
   */
  protected $migrationIds = ['custom_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->createUser(['view migration messages']);
    $this->drupalLogin($user);
    $this->database = \Drupal::database();
  }

  /**
   * Tests the overview page for migrate messages.
   *
   * Tests the overview page with the following scenarios;
   * - No message tables.
   * - With message tables.
   */
  public function testOverview(): void {
    $session = $this->assertSession();

    // First, test with no source database or message tables.
    $this->drupalGet('/admin/reports/migration-messages');
    $session->titleEquals('Migration messages | Drupal');
    $session->pageTextContainsOnce('There are no migration message tables.');

    // Create map and message tables.
    $this->createTables($this->migrationIds);

    // Now, test with message tables.
    $this->drupalGet('/admin/reports/migration-messages');
    foreach ($this->migrationIds as $migration_id) {
      $session->pageTextContains($migration_id);
    }
  }

  /**
   * Tests the detail pages for migrate messages.
   *
   * Tests the detail page with the following scenarios;
   * - No source database connection or message tables with a valid and an
   *   invalid migration.
   * - A source database connection with message tables with a valid and an
   *   invalid migration.
   * - A source database connection with message tables and a source plugin
   *   that does not have a description for a source ID in the values returned
   *   from fields().
   */
  public function testDetail(): void {
    $session = $this->assertSession();

    // Details page with invalid migration.
    $this->drupalGet('/admin/reports/migration-messages/invalid');
    $session->statusCodeEquals(404);

    // Details page with valid migration.
    $this->drupalGet('/admin/reports/migration-messages/custom_test');
    $session->statusCodeEquals(404);

    // Create map and message tables.
    $this->createTables($this->migrationIds);

    $not_available_text = "When there is an error processing a row, the migration system saves the error message but not the source ID(s) of the row. That is why some messages in this table have 'Not available' in the source ID column(s).";

    // Test details page for each migration.
    foreach ($this->migrationIds as $migration_id) {
      $this->drupalGet("/admin/reports/migration-messages/$migration_id");
      $session->pageTextContains($migration_id);
      if ($migration_id == 'custom_test') {
        $session->pageTextContains('Not available');
        $session->pageTextContains($not_available_text);
      }
    }

    // Details page with invalid migration.
    $this->drupalGet('/admin/reports/migration-messages/invalid');
    $session->statusCodeEquals(404);

    // Details page for a migration without a map table.
    $this->database->schema()->dropTable('migrate_map_custom_test');
    $this->drupalGet('/admin/reports/migration-messages/custom_test');
    $session->statusCodeEquals(404);

    // Details page for a migration with a map table but no message table.
    $this->createTables($this->migrationIds);
    $this->database->schema()->dropTable('migrate_message_custom_test');
    $this->drupalGet('/admin/reports/migration-messages/custom_test');
    $session->pageTextContains('The message table is missing for this migration.');
  }

  /**
   * Creates map and message tables for testing.
   *
   * @see \Drupal\migrate\Plugin\migrate\id_map\Sql::ensureTables
   */
  protected function createTables($migration_ids) {
    foreach ($migration_ids as $migration_id) {
      $map_table_name = "migrate_map_$migration_id";
      $message_table_name = "migrate_message_$migration_id";

      if (!$this->database->schema()->tableExists($map_table_name)) {
        $fields = [];
        $fields['source_ids_hash'] = [
          'type' => 'varchar',
          'length' => '64',
          'not null' => TRUE,
        ];
        $fields['sourceid1'] = [
          'type' => 'varchar',
          'length' => '255',
          'not null' => TRUE,
        ];
        $fields['destid1'] = [
          'type' => 'varchar',
          'length' => '255',
          'not null' => FALSE,
        ];
        $fields['source_row_status'] = [
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateIdMapInterface::STATUS_IMPORTED,
        ];
        $fields['rollback_action'] = [
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => MigrateIdMapInterface::ROLLBACK_DELETE,
        ];
        $fields['last_imported'] = [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ];
        $fields['hash'] = [
          'type' => 'varchar',
          'length' => '64',
          'not null' => FALSE,
        ];
        $schema = [
          'description' => '',
          'fields' => $fields,
          'primary key' => ['source_ids_hash'],
        ];
        $this->database->schema()->createTable($map_table_name, $schema);

        $rows = [
          [
            'source_ids_hash' => '37c655d',
            'sourceid1' => 'navigation',
            'destid1' => 'tools',
            'source_row_status' => '0',
            'rollback_action' => '1',
            'last_imported' => '0',
            'hash' => '',
          ],
          [
            'source_ids_hash' => '3a34190',
            'sourceid1' => 'menu-fixedlang',
            'destid1' => 'menu-fixedlang',
            'source_row_status' => '0',
            'rollback_action' => '0',
            'last_imported' => '0',
            'hash' => '',
          ],
          [
            'source_ids_hash' => '3e51f67',
            'sourceid1' => 'management',
            'destid1' => 'admin',
            'source_row_status' => '0',
            'rollback_action' => '1',
            'last_imported' => '0',
            'hash' => '',
          ],
          [
            'source_ids_hash' => '94a5caa',
            'sourceid1' => 'user-menu',
            'destid1' => 'account',
            'source_row_status' => '0',
            'rollback_action' => '1',
            'last_imported' => '0',
            'hash' => '',
          ],
          [
            'source_ids_hash' => 'c0efbcca',
            'sourceid1' => 'main-menu',
            'destid1' => 'main',
            'source_row_status' => '0',
            'rollback_action' => '1',
            'last_imported' => '0',
            'hash' => '',
          ],
          [
            'source_ids_hash' => 'f64cb72f',
            'sourceid1' => 'menu-test-menu',
            'destid1' => 'menu-test-menu',
            'source_row_status' => '0',
            'rollback_action' => '0',
            'last_imported' => '0',
            'hash' => '',
          ],
        ];
        foreach ($rows as $row) {
          $this->database->insert($map_table_name)->fields($row)->execute();
        }
      }

      if (!$this->database->schema()->tableExists($message_table_name)) {
        $fields = [];
        $fields['msgid'] = [
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
        ];
        $fields['source_ids_hash'] = [
          'type' => 'varchar',
          'length' => '64',
          'not null' => TRUE,
        ];
        $fields['level'] = [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 1,
        ];
        $fields['message'] = [
          'type' => 'text',
          'size' => 'medium',
          'not null' => TRUE,
        ];
        $schema = [
          'description' => '',
          'fields' => $fields,
          'primary key' => ['msgid'],
        ];
        $this->database->schema()->createTable($message_table_name, $schema);

        $rows = [
          [
            'msgid' => '1',
            'source_ids_hash' => '28cfb3d1',
            'level' => '1',
            'message' => 'Config entities can not be stubbed.',
          ],
          [
            'msgid' => '2',
            'source_ids_hash' => '28cfb3d1',
            'level' => '1',
            'message' => 'Config entities can not be stubbed.',
          ],
          [
            'msgid' => '3',
            'source_ids_hash' => '05914d93',
            'level' => '1',
            'message' => 'Config entities can not be stubbed.',
          ],
          [
            'msgid' => '4',
            'source_ids_hash' => '05914d93',
            'level' => '1',
            'message' => 'Config entities can not be stubbed.',
          ],
        ];
        foreach ($rows as $row) {
          $this->database->insert($message_table_name)->fields($row)->execute();
        }
      }
    }
  }

}
