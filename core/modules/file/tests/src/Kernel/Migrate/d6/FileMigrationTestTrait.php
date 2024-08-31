<?php

declare(strict_types=1);

namespace Drupal\Tests\file\Kernel\Migrate\d6;

/**
 * Helper for setting up a file migration test.
 */
trait FileMigrationTestTrait {

  /**
   * Setup and execute d6_file migration.
   */
  protected function setUpMigratedFiles() {
    $this->installEntitySchema('file');
    $this->installConfig(['file']);

    $this->executeMigration('d6_file', $this->fileConfiguration());
  }

  /**
   * Returns an array of migration configuration for file migrations.
   */
  protected function fileConfiguration(): array {
    // File migrations need a source_base_path.
    // @see MigrateUpgradeRunBatch::run
    return [
      'source' => [
        'site_path' => 'core/tests/fixtures',
        'constants' => [
          'source_base_path' => $this->root,
        ],
      ],
    ];
  }

}
