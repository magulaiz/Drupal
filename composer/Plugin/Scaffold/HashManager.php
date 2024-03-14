<?php

namespace Drupal\Composer\Plugin\Scaffold;

use Composer\IO\IOInterface;
use Drupal\Composer\Plugin\Scaffold\Operations\ScaffoldFileCollection;
use Drupal\Composer\Plugin\Scaffold\Operations\SkipOp;
use Composer\Json\JsonFile;

/**
 * Keeps a record of scaffold files and their hash values to detect changes.
 */
class HashManager {

  /**
   * Path to the vendor directory.
   *
   * @var string
   */
  protected $vendorDir;

  /**
   * Composer's I/O service.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * The directory where the project is located.
   *
   * @var string
   */
  protected $dir;

  /**
   * Cached list of hashes.
   *
   * @var array
   */
  protected $hashCache = [];

  const SCAFFOLD_DOCUMENTATION = 'https://www.drupal.org/docs/develop/using-composer/using-drupals-composer-scaffold';

  /**
   * Constructs a HashManager.
   *
   * Use HashManager::create instead.
   *
   * @param \Composer\IO\IOInterface $io
   *   The input/output object.
   * @param string $vendor_dir
   *   The location of the vendor directory.
   * @param string $dir
   *   The directory where the project is located.
   */
  private function __construct(IOInterface $io, $vendor_dir, $dir) {
    $this->io = $io;
    $this->vendorDir = $vendor_dir;
    $this->dir = $dir;
  }

  /**
   * Creates a HashManager.
   *
   * @param \Composer\IO\IOInterface $io
   *   The input/output object.
   * @param string $vendor_dir
   *   The location of the vendor directory.
   * @param string $dir
   *   The directory where the project is located.
   *
   * @return static
   *   A loaded hash manager.
   */
  public static function create(IOInterface $io, $vendor_dir, $dir) {
    $hash_manager = new static($io, $vendor_dir, $dir);
    $hash_path = $hash_manager->pathToHashCache();
    if (file_exists($hash_path)) {
      $contents = json_decode(file_get_contents($hash_path), TRUE) + ['hashes' => []];
      $hash_manager->hashCache = $contents['hashes'];
    }
    return $hash_manager;
  }

  /**
   * Returns the list of scaffold files which have been modified.
   *
   * If a scaffold file exists on disk, and it also has an entry in the hash
   * manager (because it was scaffolded on a previous run), then we will check
   * to see if its hash value has changed. Any file with a modified hash value
   * will be included in the result of this method.
   *
   * @param \Drupal\Composer\Plugin\Scaffold\Operations\ScaffoldFileCollection $scaffold_files
   *   Collection of destination paths.
   *
   * @return string[]
   *   List of modified files.
   */
  public function getModified(ScaffoldFileCollection $scaffold_files) {
    // Iterate over our scaffold files and determine if any were modified.
    $checked = [];
    $modified = [];
    foreach ($scaffold_files as $project_files) {
      foreach ($project_files as $destination_rel_path => $scaffold_file) {
        if ((!$scaffold_file->op() instanceof SkipOp) && !in_array($destination_rel_path, $checked)) {
          $checked[] = $destination_rel_path;
          if ($this->checkModified($scaffold_file->destination())) {
            $modified[] = $destination_rel_path;
          }
        }
      }
    }

    // Let the caller know what the user decided to keep.
    return $modified;
  }

  /**
   * Checks to see if a single scaffold file has been modified on disk.
   *
   * If the file existed on the last run and its hash has changed, then add it
   * to the list of modified files.
   *
   * @param \Drupal\Composer\Plugin\Scaffold\ScaffoldFilePath $scaffold_file
   *   One scaffold file that is about to be placed.
   *
   * @return bool
   *   True if file has been modified since it was scaffolded.
   */
  protected function checkModified(ScaffoldFilePath $scaffold_file) {
    $relative = $scaffold_file->relativePath();
    if (!isset($this->hashCache[$relative])) {
      return FALSE;
    }
    $hash = $this->hashFile($scaffold_file);
    if (empty($hash)) {
      return FALSE;
    }
    return $hash != $this->hashCache[$relative];
  }

  /**
   * Returns the list of scaffold files which have been modified.
   *
   * If a scaffold file exists on disk, and it also has an entry in the hash
   * manager (because it was scaffolded on a previous run), then we will check
   * to see if its hash value has changed. Any file with a modified hash value
   * will be included in the result of this method.
   *
   * @param \Drupal\Composer\Plugin\Scaffold\Operations\ScaffoldFileCollection $scaffold_files
   *   Collection of destination paths.
   *
   * @return string[]
   *   List of modified files.
   */
  public function getUnchanged(ScaffoldFileCollection $scaffold_files) {
    // Iterate over our scaffold files and determine if any were modified.
    $checked = [];
    $unchanged = [];
    foreach ($scaffold_files as $project_files) {
      foreach ($project_files as $destination_rel_path => $scaffold_file) {
        if ((!$scaffold_file->op() instanceof SkipOp) && !in_array($destination_rel_path, $checked)) {
          $checked[] = $destination_rel_path;
          if ($this->checkUnchanged($scaffold_file)) {
            $unchanged[] = $destination_rel_path;
          }
        }
      }
    }

    // Let the caller know what the user decided to keep.
    return $unchanged;
  }

  /**
   * Checks to see if a scaffold source file has changed since it was written.
   *
   * Note that "unchanged since written" only applies in instances where the
   * file written to disk still exists. If it has been removed completely, then
   * we will not consider the source to be "unchanged since written".
   *
   * @param ScaffoldFileInfo $scaffold_file
   *   The scaffold file to check.
   *
   * @return bool
   *   TRUE if we know for sure that the source is unchanged.
   */
  protected function checkUnchanged(ScaffoldFileInfo $scaffold_file) {
    $relative = $scaffold_file->destination()->relativePath();
    $path = $scaffold_file->destination()->fullPath();
    if (!isset($this->hashCache[$relative]) || !file_exists($path) || getenv('DRUPAL_SCAFFOLD_DISCARD_MODIFIED') || getenv('DRUPAL_SCAFFOLD_KEEP_MODIFIED') || getenv('DRUPAL_SCAFFOLD_RESCAFFOLD')) {
      return FALSE;
    }
    $new_hash = sha1($scaffold_file->op()->contents());
    return $new_hash == $this->hashCache[$relative];
  }

  /**
   * Asks the user how to handle modified files.
   *
   * As a side effect of this function, the root-level composer.json file will
   * be modified if the user selects the "keep" option.
   *
   * @param string[] $modified
   *   An array of destination paths that were modified.
   *
   * @return string[]
   *   An array of modified that should not be preserved.
   */
  public function decideHowToHandleModified(array $modified) {
    // Nothing modified? Nothing to do.
    if (empty($modified)) {
      return [];
    }

    // Show the user which files are in danger of being overwritten.
    $show_modified = implode("\n", array_map(function ($item) {
      return "  - <info>$item</info>";
    }, $modified));
    $this->io->writeError("The following managed scaffold files have been modified:\n$show_modified\n");

    // Ask the user what they want to do.
    while (TRUE) {
      $default = $this->defaultAnswerForModifiedPrompt();

      // @todo Composer also offers 'v' (view) and 'd' (diff); we could add
      // 'view' as a follow-on task, but 'diff' would be difficult here.
      switch ($this->io->ask('    <info>Discard changes [y,n,a,?]?</info> ', $default)) {
        case 'y':
          return [];

        case 'n':
          $this->overrideModifiedInComposerJson($modified);
          return $modified;

        case 'a':
          throw new \RuntimeException('Scaffold aborted.');

        case '?':
        default:
          $this->io->writeError([
            '    y - discard changes and rewrite scaffold files',
            '    n - keep modified files in their current state; ask again the next time Composer runs',
            '    k - keep modified files and modify composer.json to avoid being asked again',
            '    a - abort scaffold operation',
            '    For information on how to manage scaffold modifications, see:',
            '    ' . self::SCAFFOLD_DOCUMENTATION,
          ]);
      }
    }
  }

  /**
   * Determines what our default action should be for prompt.
   *
   * The default value is returned automatically in non-interactive mode, or
   * if the user hits [RETURN] without making a selection.
   *
   * @return string
   *   Default value to return from 'modified' prompt.
   */
  protected function defaultAnswerForModifiedPrompt() {
    // Interactive? Ignore environment variables and always default to '?'.
    if ($this->io->isInteractive()) {
      return '?';
    }
    // Keep modified files and don't ask again.
    if (getenv('DRUPAL_SCAFFOLD_KEEP_MODIFIED')) {
      return 'n';
    }
    // Discard modified if instructed.
    if (getenv('DRUPAL_SCAFFOLD_DISCARD_MODIFIED')) {
      return 'y';
    }
    // Default is to abort the scaffold operaion.
    return 'a';
  }

  /**
   * Modifies composer.json to preserve modified files on subsequent runs.
   *
   * @param array $modified
   *   List of modified packages to disable in the composer.json file mappings.
   */
  protected function overrideModifiedInComposerJson(array $modified) {
    $composer_json_file = new JsonFile($this->dir . '/composer.json');

    $composer_json_data = $composer_json_file->read();
    foreach ($modified as $keepPackage) {
      $composer_json_data['extra']['drupal-scaffold']['file-mapping'][$keepPackage] = FALSE;
    }
    $composer_json_file->write($composer_json_data);
  }

  /**
   * Stores the provided list of file paths to the hash cache file.
   *
   * @param \Drupal\Composer\Plugin\Scaffold\Operations\ScaffoldResult[] $files
   *   A list of scaffold results, each of which holds a path.
   */
  public function storeResultsHash(array $files) {
    foreach ($files as $scaffold_result) {
      if ($scaffold_result->isManaged()) {
        $hash = $this->hashFile($scaffold_result->destination());
        $relative = $scaffold_result->destination()->relativePath();
        $this->hashCache[$relative] = $hash;
      }
    }
    $hash_path = $this->pathToHashCache();
    $contents = [
      '_README' => $this->getHashCacheReadme(),
      'hashes' => $this->hashCache,
    ];
    file_put_contents($hash_path, json_encode($contents));
  }

  /**
   * Returns the _README contents to be stored in the hash cache file.
   *
   * @return string
   *   Comment to be written into the hash cache.
   */
  protected function getHashCacheReadme() {
    $docs_url = self::SCAFFOLD_DOCUMENTATION;
    return <<< __EOT__
This file contains a list of hashes of the contents of all scaffold files
managed on this site. It is recommended that this file be .gitignore'd, although
it is not harmful to commit it.

For details on how to correctly manage customizations to a site's scaffold
files, see $docs_url.
__EOT__;
  }

  /**
   * Gets the path to the file where the list of hashes is stored.
   *
   * @return string
   *   Path where the list of scaffolded files and their hashes are stored.
   */
  protected function pathToHashCache() {
    return dirname($this->vendorDir) . "/.scaffolded.json";
  }

  /**
   * Calculates the on-disk hash value of a scaffold file.
   *
   * @param \Drupal\Composer\Plugin\Scaffold\ScaffoldFilePath $scaffold_file
   *   Path to the scaffold file, at its destination path.
   *
   * @return string
   *   Hash value.
   */
  protected function hashFile(ScaffoldFilePath $scaffold_file) {
    $path = $scaffold_file->fullPath();
    if (!file_exists($path)) {
      return '';
    }
    return sha1_file($path);
  }

}
