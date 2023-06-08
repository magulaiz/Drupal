<?php

namespace Drupal\Composer\Plugin\VendorHardening;

/**
 * Provides file security functions.
 *
 * IMPORTANT: This file is duplicated at /lib/Drupal/Component/FileSecurity.
 * If any change is made here, the same change should be made in the duplicate.
 * See https://www.drupal.org/project/drupal/issues/3079481.
 *
 * @internal
 */
class FileSecurity {

  /**
   * Writes an .htaccess file in the given directory, if it doesn't exist.
   *
   * @param string $directory
   *   The directory.
   * @param bool $deny_public_access
   *   (optional) Set to FALSE to ensure an .htaccess file for an open and
   *   public directory. Default is TRUE.
   * @param bool $force
   *   (optional) Set to TRUE to force overwrite an existing file.
   *
   * @return bool
   *   TRUE if the file already exists or was created. FALSE otherwise.
   */
  public static function writeHtaccess($directory, $deny_public_access = TRUE, $force = FALSE) {
    $source = match($deny_public_access) {
      default => __DIR__ . '/scaffold/htaccess-private',
      FALSE => __DIR__ . '/scaffold/htaccess',
    };
    return self::copyFile($directory, '.htaccess', $source, $force);
  }

  /**
   * Writes a web.config file in the given directory, if it doesn't exist.
   *
   * @param string $directory
   *   The directory.
   * @param bool $force
   *   (optional) Set to TRUE to force overwrite an existing file.
   *
   * @return bool
   *   TRUE if the file already exists or was created. FALSE otherwise.
   */
  public static function writeWebConfig($directory, $force = FALSE) {
    return self::copyFile($directory, 'web.config', __DIR__ . '/scaffold/web.config', $force);
  }

  /**
   * Copies the file to the given directory.
   *
   * @param string $directory
   *   The directory to write to.
   * @param string $filename
   *   The file name.
   * @param string $source
   *   The source file path.
   * @param bool $force
   *   TRUE if we should force the write over an existing file.
   *
   * @return bool
   *   TRUE if writing the file was successful.
   */
  protected static function copyFile($directory, $filename, $source, $force) {
    $file_path = $directory . DIRECTORY_SEPARATOR . $filename;
    // Don't overwrite if the file exists unless forced.
    if (file_exists($file_path) && !$force) {
      return TRUE;
    }
    // Try to write the file. This can fail if concurrent requests are both
    // trying to write a the same time.
    if (@!copy($source, $file_path)) {
      return FALSE;
    }
    return @chmod($file_path, 0444);
  }

}
