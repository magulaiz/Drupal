<?php

namespace Drupal\Composer\Generator;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Drupal\Composer\Composer;
use Drupal\Composer\Generator\Util\DrupalCoreComposer;
use Symfony\Component\Finder\Finder;

/**
 * Reconciles Drupal component dependencies with core.
 *
 * @see PackageGenerator
 */
class ComponentGenerator {

  /**
   * Relative path from Drupal root to the component directory.
   *
   * @var string
   */
  protected static $relativeComponentPath = 'core/lib/Drupal/Component';

  /**
   * Base directory where generated projects are written.
   *
   * @var string
   */
  protected $generatedProjectBaseDir;

  /**
   * Data from drupal/drupal's composer.json file.
   *
   * @var array
   */
  protected $drupalProjectInfo;

  /**
   * Data from drupal/core's composer.json file.
   *
   * @var array
   */
  protected $drupalCoreInfo;

  /**
   * ComponentGenerator constructor.
   */
  public function __construct() {
    $this->generatedProjectBaseDir = dirname(__DIR__, 2) . '/' . static::$relativeComponentPath;
  }

  /**
   * @return \Symfony\Component\Finder\Finder
   */
  public function getComponentPathsFinder() {
    $data = [];
    $composer_json_finder = new Finder();
    $composer_json_finder->name('composer.json')
      ->in($this->generatedProjectBaseDir)
      ->ignoreUnreadableDirs()
      ->depth(1);
    return $composer_json_finder;
  }

  /**
   * Generate Drupal's metapackages whenever composer.lock is updated.
   *
   * @param \Composer\IO\IOInterface $io
   *   Composer IO object for interacting with the user.
   * @param string $base_dir
   *   Directory where drupal/drupal repository is located.
   */
  public function generate(IOInterface $io, $base_dir) {
    // General information from drupal/drupal and drupal/core composer.json
    // and composer.lock files.
    $this->drupalProjectInfo = DrupalCoreComposer::createFromPath($base_dir);
    $this->drupalCoreInfo = DrupalCoreComposer::createFromPath($base_dir . '/core');

    $changed = FALSE;
    /** @var \Symfony\Component\Finder\SplFileInfo $component_path */
    foreach ($this->getComponentPathsFinder()->getIterator() as $component_path) {
      $changed |= $this->generateComponentPackage($io, $component_path->getRelativePath());
    }

    // Remind the user not to miss files in a patch.
    if ($changed) {
      $io->write("If you make a patch, ensure that the files above are included.");
    }
  }

  /**
   * Generate the component JSON files.
   *
   * @param \Composer\IO\IOInterface $io
   *   Composer IO object for interacting with the user.
   * @param string $component_path
   *   Individual relative component path, such as Utility or Render.
   *
   * @return bool
   *   TRUE if the generated component package is different than what is on disk.
   */
  protected function generateComponentPackage(IOInterface $io, $component_path) {
    $composer_json_path = $this->generatedProjectBaseDir . '/' . $component_path . '/composer.json';
    $original_composer_json = file_exists($composer_json_path) ? file_get_contents($composer_json_path) : '';

    // Modify the original data.
    $composer_json_data = $this->getPackage($original_composer_json);
    $updated_composer_json = static::encode($composer_json_data);

    // Exit early if nothing changed.
    if (trim($original_composer_json, " \t\r\0\x0B") == trim($updated_composer_json, " \t\r\0\x0B")) {
      return FALSE;
    }

    // Warn the user that a metapackage file has been updated.
    $display_path = static::$relativeComponentPath . '/' . $component_path . '/composer.json';
    $io->write("Updated component file <info>$display_path</info>.");

    // Write the composer.json file back to disk
    $fs = new Filesystem();
    $fs->ensureDirectoryExists(dirname($composer_json_path));
    file_put_contents($composer_json_path, $updated_composer_json);

    return TRUE;
  }

  /**
   * Reconcile JSON data.
   *
   * @param string $original_json
   *   Contents of the component's composer.json file.
   *
   * @return array
   *   Structured data to be turned back into JSON.
   */
  protected function getPackage($original_json) {
    $original_data = json_decode($original_json, TRUE);
    $package_data = array_merge($original_data, $this->initialPackageMetadata());

    $core_info = $this->drupalCoreInfo->rootComposerJson();

    // Assume that if Drupal is a dev version, then minimum stability for
    // components is also dev. This allows testing in-situ with path-based
    // Composer repositories.
    if (strpos(Composer::drupalVersionBranch(), '-') !== FALSE) {
      $package_data['minimum-stability'] = 'dev';
    }

    // Traverse required packages.
    foreach (array_keys($original_data['require'] ?? []) as $package_name) {
      // Reconcile locked constraints from drupal/drupal. We might have a locked
      // version of a dependency that's not present in drupal/core.
      if ($info = $this->drupalProjectInfo->packageLockInfo($package_name)) {
        $package_data['require'][$package_name] = $info['version'];
      }

      // Reconcile looser constraints from drupal/core, and we're totally OK
      // with over-writing the locked ones from above.
      if ($constraint = $core_info['require'][$package_name] ?? FALSE) {
        $package_data['require'][$package_name] = $constraint;
      }

      // Reconcile dependencies on other Drupal components, so we can set the
      // constraint to our current version.
      if (strpos($package_name, 'drupal/core-') !== FALSE) {
        $package_data['require'][$package_name] = Composer::drupalVersionBranch();
      }
    }

    return $package_data;
  }

  /**
   * Utility function to encode metapackage json in a consistent way.
   *
   * @param array $composer_json_data
   *   Data to encode into a json string.
   *
   * @return string
   *   Encoded version of provided json data.
   */
  public static function encode($composer_json_data) {
    return json_encode($composer_json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
  }

  /**
   * Common default metadata for all components.
   *
   * @todo Add change record link.
   *
   * @return array
   */
  protected function initialPackageMetadata() {
    return [
      'extra' => [
        '_readme' => [
          'This file was partially generated automatically. See: [change record]',
        ],
      ],
    ];
  }

}
