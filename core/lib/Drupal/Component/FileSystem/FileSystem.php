<?php

namespace Drupal\Component\FileSystem;

/**
 * Provides file system functions.
 */
class FileSystem {

  /**
   * Discovers a writable system-appropriate temporary directory.
   *
   * @return string|false
   *   A string containing the path to the temporary directory, or FALSE if no
   *   suitable temporary directory can be found.
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   sys_get_temp_dir() instead.
   *
   * @see https://www.drupal.org/node/3225275
   */
  public static function getOsTemporaryDirectory() {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use sys_get_temp_dir() instead. See https://www.drupal.org/node/3225275', E_USER_DEPRECATED);
    $directories = [];

    // Has PHP been set with an upload_tmp_dir?
    if (ini_get('upload_tmp_dir')) {
      $directories[] = ini_get('upload_tmp_dir');
    }

    // Operating system specific dirs.
    if (substr(PHP_OS, 0, 3) == 'WIN') {
      $directories[] = 'c:\\windows\\temp';
      $directories[] = 'c:\\winnt\\temp';
    }
    else {
      $directories[] = '/tmp';
    }
    // PHP may be able to find an alternative tmp directory.
    $directories[] = sys_get_temp_dir();

    foreach ($directories as $directory) {
      if (is_dir($directory) && is_writable($directory)) {
        // Both sys_get_temp_dir() and ini_get('upload_tmp_dir') can return paths
        // with a trailing directory separator.
        return rtrim($directory, DIRECTORY_SEPARATOR);
      }
    }
    return FALSE;
  }

}
