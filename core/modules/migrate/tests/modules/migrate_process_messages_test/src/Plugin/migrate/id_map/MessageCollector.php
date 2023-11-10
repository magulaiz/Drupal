<?php

namespace Drupal\migrate_process_messages_test\Plugin\migrate\id_map;

use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\migrate\id_map\NullIdMap;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Test ID map which passes messages to the message service.
 *
 * Intended for use with tests which extend MigrateTestBase.
 *
 * @PluginID("test_message_collector")
 */
class MessageCollector extends NullIdMap {

  /**
   * The message service.
   *
   * @var \Drupal\migrate\MigrateMessageInterface
   */
  protected $message;

  /**
   * {@inheritdoc}
   */
  public function setMessage(MigrateMessageInterface $message) {
    $this->message = $message;
  }

  /**
   * {@inheritdoc}
   */
  public function saveMessage(array $source_id_values, $message, $level = MigrationInterface::MESSAGE_ERROR) {
    $this->message->display($message, $level);
  }

}
