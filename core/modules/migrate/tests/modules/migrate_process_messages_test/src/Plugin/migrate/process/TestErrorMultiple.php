<?php

namespace Drupal\migrate_process_messages_test\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Test process plugin which always throws an exception.
 *
 * @MigrateProcessPlugin(
 *   id = "test_error_multiple",
 *   handle_multiples = TRUE,
 * )
 */
class TestErrorMultiple extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    throw new MigrateException('Process exception.');
  }

}
