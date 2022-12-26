<?php

namespace Drupal\Component\PhpStorage;

use Drupal\Component\FileSecurity\FileSecurity;

/**
 * Stores the code as regular PHP files.
 */
class FileStorage implements PhpStorageInterface {

  /**
   * The directory where the files should be stored.
   *
   * @var string
   */
  protected $directory;

  /**
   * Constructs this FileStorage object.
   *
   * @param array $configuration
   *   An associative array, containing at least these two keys:
   *   - directory: The directory where the files should be stored.
   *   - bin: The storage bin. Multiple storage objects can be instantiated with
   *     the same configuration, but for different bins..
   */
  public function __construct(array $configuration) {
    $this->directory = $configuration['directory'] . '/' . $configuration['bin'];
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return file_exists($this->getFullPath($name));
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    // The FALSE returned on failure is enough for the caller to handle this,
    // we do not want a warning too.
    return (@include_once $this->getFullPath($name)) !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function save($name, $code) {
    $path = $this->getFullPath($name);
    $directory = dirname($path);
    $this->ensureDirectory($directory);
    return (bool) file_put_contents($path, $code);
  }

  /**
   * Ensures the directory exists, has the right permissions, and a .htaccess.
   *
   * For compatibility with open_basedir, the requested directory is created
   * using a recursion logic that is based on the relative directory path/tree:
   * It works from the end of the path recursively back towards the root
   * directory, until an existing parent directory is found. From there, the
   * subdirectories are created.
   *
   * @param string $directory
   *   The directory path.
   * @param int $mode
   *   The mode, permissions, the directory should have.
   */
  protected function ensureDirectory($directory, $mode = 0777) {
    if ($this->createDirectory($directory, $mode)) {
      FileSecurity::writeHtaccess($directory);
    }
  }

  /**
   * Ensures the requested directory exists and has the right permissions.
   *
   * For compatibility with open_basedir, the requested directory is created
   * using a recursion logic that is based on the relative directory path/tree:
   * It works from the end of the path recursively back towards the root
   * directory, until an existing parent directory is found. From there, the
   * subdirectories are created.
   *
   * @param string $directory
   *   The directory path.
   * @param int $mode
   *   The mode, permissions, the directory should have.
   *
   * @return bool
   *   TRUE if the directory exists or has been created, FALSE otherwise.
   */
  protected function createDirectory($directory, $mode = 0777) {
    // If the directory exists already, there's nothing to do.
    if (is_dir($directory)) {
      return TRUE;
    }

    // If the parent directory doesn't exist, try to create it.
    $parent_exists = is_dir($parent = dirname($directory));
    if (!$parent_exists) {
      $parent_exists = $this->createDirectory($parent, $mode);
    }

    // If parent exists, try to create the directory and ensure to set its
    // permissions, because mkdir() obeys the umask of the current process.
    if ($parent_exists) {
      // We hide warnings and ignore the return because there may have been a
      // race getting here and the directory could already exist.
      @mkdir($directory);
      // Only try to chmod() if the subdirectory could be created.
      if (is_dir($directory)) {
        // Avoid writing permissions if possible.
        if ($this->standardizePermissionsString($mode) !== $this->standardizePermissionsString(fileperms($directory), TRUE)) {
          return chmod($directory, $mode);
        }
        return TRUE;
      }
      else {
        // Something failed and the directory doesn't exist.
        trigger_error('mkdir(): Permission Denied', E_USER_WARNING);
      }
    }
    return FALSE;
  }

  /**
   * Ensures we have sensible permission strings to compare.
   *
   * Decimal numbers are converted to octal. It is assumed that any non-zero
   * number in the 5th position should not be there in octal form and is
   * removed. This will make a result from fileperms() compatible with normal
   * permissions because it adds 040000 (seen once converted to octal) to
   * specify a directory which we don't want when doing comparisons of
   * permissions strings.
   *
   * We allow the option to ignore special permissions bits which will all be in
   * the 4th octal place because most of the time this is something an admin
   * should be able to change without Drupal being aware.
   *
   * Once in octal form we make sure there are enough leading zeros for a
   * string of 4 characters minimum but no more than one leading zero if it goes
   * past 4 characters. A leading zero is necessary for PHP to know that it is
   * an octal string.
   *
   * The final string should be either 4 or 5 characters long depending on
   * whether special bits are set namely: sticky, SUID, SGID; unless we are
   * ignoring them.
   *
   * This formatting is so that we can do a useful string comparison of
   * permissions between default or user defined which will typically be like:
   * 0775, 0755 and what fileperms() returns which will typically be 040775
   * (in octal) for a directory but would be fine for a normal file.
   *
   * Note: We could have done a conversion to a number instead of string however
   *       this format is what we are used to seeing when it comes to
   *       permissions and so makes it a little easier to debug when necessary.
   *
   * @param int|string $num
   *   Permissions either as a string or a number.
   * @param bool $base10
   *   Says whether the string is decimal or octal.
   * @param bool $ignoreSpecialBits
   *   Allows us to ignore bits in the 4th position of an octal string which
   *   are all special permission bits.
   *
   * @return string
   *   Our standardized octal string.
   */
  protected function standardizePermissionsString($num, bool $base10 = FALSE, bool $ignoreSpecialBits = TRUE): string {
    if ($base10) {
      $num = decoct($num);
    }

    return str_pad(
      preg_replace('/^0*/m', '0', substr($num, -($ignoreSpecialBits ? 3 : 4))),
      3,
      '0',
      STR_PAD_LEFT);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    $path = $this->getFullPath($name);
    if (file_exists($path)) {
      return $this->unlink($path);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFullPath($name) {
    return $this->directory . '/' . $name;
  }

  /**
   * {@inheritdoc}
   */
  public function writeable() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    return $this->unlink($this->directory);
  }

  /**
   * Deletes files and/or directories in the specified path.
   *
   * If the specified path is a directory the method will
   * call itself recursively to process the contents. Once the contents have
   * been removed the directory will also be removed.
   *
   * @param string $path
   *   A string containing either a file or directory path.
   *
   * @return bool
   *   TRUE for success or if path does not exist, FALSE in the event of an
   *   error.
   */
  protected function unlink($path) {
    if (file_exists($path)) {
      if (is_dir($path)) {
        // Ensure the folder is writable.
        @chmod($path, 0777);
        foreach (new \DirectoryIterator($path) as $fileinfo) {
          if (!$fileinfo->isDot()) {
            $this->unlink($fileinfo->getPathName());
          }
        }
        return @rmdir($path);
      }
      // Windows needs the file to be writable.
      @chmod($path, 0700);
      return @unlink($path);
    }
    // If there's nothing to delete return TRUE anyway.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    $names = [];
    if (file_exists($this->directory)) {
      foreach (new \DirectoryIterator($this->directory) as $fileinfo) {
        if (!$fileinfo->isDot()) {
          $name = $fileinfo->getFilename();
          if ($name != '.htaccess') {
            $names[] = $name;
          }
        }
      }
    }
    return $names;
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
  }

}
