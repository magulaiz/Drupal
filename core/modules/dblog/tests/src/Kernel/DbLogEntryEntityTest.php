<?php

namespace Drupal\Tests\dblog\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\dblog\Functional\FakeLogEntries;
use Drupal\dblog\Entity\DblogEntry;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Test the dblog entries entity functionality.
 *
 * @group dblog
 */
class DbLogEntryEntityTest extends KernelTestBase {

  use FakeLogEntries;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('dblog', ['watchdog']);
    $this->installSchema('system', ['sequences']);
    $this->installConfig(['system']);
  }

  /**
   * Tests creation of log entries.
   */
  public function testCreate() {
    $log = DblogEntry::create([
      'uid' => 1,
      'type' => 'system',
      'message' => 'Test @a',
      'variables' => serialize(['@a' => 'b']),
      'severity' => RfcLogLevel::NOTICE,
      'link' => 'node/1/edit',
      'location' => '',
      'referer' => '',
      'hostname' => '127.0.0.1',
      'timestamp' => 1632269259,
    ]);

    $this->assertEquals(1, $log->getUid());

    $this->assertEquals('system', $log->getType());

    $this->assertEquals(RfcLogLevel::NOTICE, $log->getSeverity());

    $this->assertEquals('node/1/edit', $log->getLink());

    $this->assertEquals('', $log->getLocation());

    $this->assertEquals('', $log->getReferer());

    $this->assertEquals('127.0.0.1', $log->getHostname());

    $this->assertEquals(1632269259, $log->getTimestamp());

    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Use dblog.logger service to create log entities.');
    $log->save();
  }

  /**
   * Tests loading a log entry.
   */
  public function testLoad() {
    $this->generateLogEntries(1);
    $log = DblogEntry::load(1);

    $this->assertEquals(1, $log->id());
    $this->assertEquals('Dblog test log message Entry #0', $log->get('message')->getString());
    $this->assertEquals(RfcLogLevel::NOTICE, $log->getSeverity());
  }

  /**
   * Tests deletion of log entries.
   */
  public function testDelete() {
    $this->generateLogEntries(1);
    $log = DblogEntry::load(1);
    $log->delete();
    $this->assertNull(DblogEntry::load(1));
  }

  /**
   * Tests that the log entries cannot be updated once created.
   */
  public function testUpdate() {
    $this->generateLogEntries(1);
    $log = DblogEntry::load(1);

    $this->expectException(EntityStorageException::class);
    $this->expectExceptionMessage('Dblog entries are read only entities. Saving them once created is not allowed.');

    $log->save();
  }

  /**
   * Tests common dblog fields.
   */
  public function testHasField() {
    $fields = [
      'wid',
      'uid',
      'type',
      'message',
      'variables',
      'severity',
      'link',
      'location',
      'referer',
      'hostname',
      'timestamp',
    ];
    $log = DblogEntry::create();
    foreach ($fields as $field_name) {
      $this->assertTrue($log->hasField($field_name));
    }

    $this->assertFalse($log->hasField('field_message'));
  }

  /**
   * Tests field definitions.
   */
  public function testFieldDefinition() {
    $fields = [
      'wid' => 'integer',
      'uid' => 'entity_reference',
      'type' => 'string',
      'message' => 'string',
      'variables' => 'string_long',
      'severity' => 'integer',
      'link' => 'string_long',
      'location' => 'string_long',
      'referer' => 'string',
      'hostname' => 'string',
      'timestamp' => 'created',
    ];

    $log = DblogEntry::create();
    foreach ($fields as $field_name => $field_type) {
      $this->assertEquals($field_name, $log->getFieldDefinition($field_name)->getName());
      $this->assertEquals($field_type, $log->getFieldDefinition($field_name)->getType());
    }

    $this->assertEquals(array_keys($log->getFields()), array_keys($fields));
    $this->assertEquals([], $log->getTranslatableFields());
  }

  /**
   * Tests format message method.
   */
  public function testFormatMessage() {
    $dblog_formatter = \Drupal::service('dblog.formatter');

    $log = DblogEntry::create();
    $this->assertEquals($log->getFormattedMessage($dblog_formatter), '');

    $log = DblogEntry::create(['message' => '<script>Test</script>']);
    $this->assertEquals($log->getFormattedMessage($dblog_formatter), 'Log data is corrupted and cannot be unserialized: Test');

    $log = DblogEntry::create([
      'message' => '<script>Test</script>',
      'variables' => serialize([]),
    ]);
    $this->assertEquals($log->getFormattedMessage($dblog_formatter), 'Test');

    $log = DblogEntry::create([
      'message' => 'Testing @module module',
      'variables' => serialize(['@module' => 'dblog']),
    ]);
    $this->assertEquals($log->getFormattedMessage($dblog_formatter), 'Testing dblog module');
  }

  /**
   * Tests validate methods.
   */
  public function testValidate() {
    $log = DblogEntry::create();
    $log->validate();
    $this->assertFalse($log->isValidationRequired());
    $log->setValidationRequired(TRUE);
    $this->assertTrue($log->isValidationRequired());
  }

  /**
   * Tests toArray method.
   */
  public function testToArray() {
    $this->generateLogEntries(1, ['timestamp' => 1632269259]);
    $log = DblogEntry::load(1);
    $this->assertEquals([
      'wid' => [
        [
          'value' => 1,
        ],
      ],
      'uid' => [
        [
          'target_id' => 0,
        ],
      ],
      'type' => [
        [
          'value' => 'custom',
        ],
      ],
      'message' => [
        [
          'value' => 'Dblog test log message Entry #0',
        ],
      ],
      'variables' => [
        [
          'value' => 'a:0:{}',
        ],
      ],
      'severity' => [
        [
          'value' => RfcLogLevel::NOTICE,
        ],
      ],
      'link' => [],
      'location' => [
        [
          'value' => 'http://localhost/',
        ],
      ],
      'referer' => [],
      'hostname' => [
        [
          'value' => '127.0.0.1',
        ],
      ],
      'timestamp' => [
        [
          'value' => 1632269259,
        ],
      ],
    ], $log->toArray());
  }

}
