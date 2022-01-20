<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\Core\File\FileSystemInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;

/**
 * Provides functionality for file process plugins.
 *
 * Available configuration keys:
 * - download_exception: (optional) Error behavior when a file cannot be
 *   downloaded successfully:
 *   - 'error': (default) Raise an error and stop the migration.
 *   - 'skip process': Prevents further processing of the input property.
 *   - 'skip row': Skips the entire row.
 * - file_exists: (optional) Replace behavior when the destination file already
 *   exists:
 *   - 'replace' - (default) Replace the existing file.
 *   - 'rename' - Append _{incrementing number} until the filename is
 *     unique.
 *   - 'use existing' - Do nothing and return FALSE.
 */
abstract class FileProcessBase extends ProcessPluginBase {

  /**
   * Constructs a file process plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    if (array_key_exists('download_exception', $configuration)) {
      switch ($configuration['download_exception']) {
        case 'skip process':
          $configuration['download_exception'] = MigrateSkipProcessException::class;
          break;

        case 'skip row':
          $configuration['download_exception'] = MigrateSkipRowException::class;
          break;

        default:
          $configuration['download_exception'] = MigrateException::class;
      }
    }
    if (array_key_exists('file_exists', $configuration)) {
      switch ($configuration['file_exists']) {
        case 'use existing':
          $configuration['file_exists'] = FileSystemInterface::EXISTS_ERROR;
          break;

        case 'rename':
          $configuration['file_exists'] = FileSystemInterface::EXISTS_RENAME;
          break;

        default:
          $configuration['file_exists'] = FileSystemInterface::EXISTS_REPLACE;
      }
    }

    $configuration += [
      'download_exception' => MigrateException::class,
      'file_exists' => FileSystemInterface::EXISTS_REPLACE,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
