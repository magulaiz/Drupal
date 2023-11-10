<?php

namespace Drupal\Tests\migrate\Kernel;

use Drupal\migrate\MigrateExecutable;

/**
 * Tests the format of messages from process plugin exceptions.
 *
 * @group migrate
 */
class MigrateProcessErrorMessagesTest extends MigrateTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'migrate_events_test',
    'migrate_process_messages_test',
    'migrate',
  ];

  /**
   * {@inheritdoc}
   */
  protected $collectMessages = TRUE;

  /**
   * Tests the format of messages from process plugin exceptions.
   */
  public function testProcessErrorMessages() {
    $definition = [
      'id' => 'process_errors',
      'idMap' => [
        'plugin' => 'test_message_collector',
      ],
      'source' => [
        'plugin' => 'embedded_data',
        'data_rows' => [],
        'ids' => ['id' => ['type' => 'integer']],
      ],
      'process' => [],
      'destination' => [
        'plugin' => 'dummy',
      ],
      'migration_dependencies' => [],
    ];

    $definition['source']['data_rows'] = [
      [
        'id' => 1,
        'name' => 'Item 1',
      ],
    ];
    $definition['process'] = [
      'id' => [
        [
          'plugin' => 'test_error_single',
          'value' => 'id',
        ],
      ],
    ];

    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);

    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $this->assertEquals("process_errors:id:test_error_single: Process exception.", $this->migrateMessages[1][0]);
    $this->migrateMessages = [];

    $definition['source']['data_rows'] = [
      [
        'id' => 1,
        'property' => [
          'subfield' => [
            42,
          ],
        ],
      ],
    ];
    $definition['process'] = [
      'id' => 'id',
      'property' => [
        [
          'plugin' => 'sub_process',
          'source' => 'property',
          'process' => [
            'subfield' => [
              [
                'plugin' => 'test_error_single',
                'value' => 'subfield',
              ],
            ],
          ],
        ],
      ],
    ];

    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration($definition);

    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $this->assertEquals("process_errors:property:sub_process: test_error_single: Process exception.", $this->migrateMessages[1][0]);
    $this->migrateMessages = [];
  }

}
