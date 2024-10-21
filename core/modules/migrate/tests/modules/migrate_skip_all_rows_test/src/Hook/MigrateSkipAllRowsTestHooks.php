<?php

namespace Drupal\migrate_skip_all_rows_test\Hook;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Plugin\MigrateSourceInterface;
use Drupal\migrate\Row;
use Drupal\Core\Hook\Attribute\Hook;
class MigrateSkipAllRowsTestHooks
{
    /**
     * Implements hook_migrate_prepare_row().
     */
    #[Hook('migrate_prepare_row')]
    public function migratePrepareRow(\Drupal\migrate\Row $row, \Drupal\migrate\Plugin\MigrateSourceInterface $source, \Drupal\migrate\Plugin\MigrationInterface $migration)
    {
        if (\Drupal::state()->get('migrate_skip_all_rows_test_migrate_prepare_row')) {
            throw new \Drupal\migrate\MigrateSkipRowException();
        }
    }
}
