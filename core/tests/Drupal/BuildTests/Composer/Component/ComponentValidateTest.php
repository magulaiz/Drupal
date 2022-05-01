<?php

namespace Drupal\BuildTests\Composer\Component;

use Drupal\BuildTests\Framework\BuildTestBase;
use Drupal\Composer\Composer;
use Symfony\Component\Finder\Finder;

/**
 * Try to install dependencies per component, using Composer.
 *
 * @group Composer
 * @group Component
 * @requires externalCommand composer
 * @covers nothing
 */
class ComponentValidateTest extends BuildTestBase {

  /**
   * Relative path from Drupal root to the Components directory.
   *
   * @var string
   */
  static protected $componentsPath = '/core/lib/Drupal/Component';

  public function provideComponentPaths() {
    $data = [];
    // During the dataProvider phase, there is not a workspace directory yet.
    // So we will find relative paths and assemble them with the workspace
    // path later.
    $drupal_root = $this->getDrupalRoot();
    $composer_json_finder = new Finder();
    $composer_json_finder->name('composer.json')
      ->in($drupal_root . static::$componentsPath)
      ->ignoreUnreadableDirs()
      ->depth(1);
    /** @var \Symfony\Component\Finder\SplFileInfo $path */
    foreach ($composer_json_finder->getIterator() as $path) {
      $data[] = ['/' . $path->getRelativePath()];
    }
    return $data;
  }

  /**
   * Test whether components' composer.json can be installed in isolation.
   *
   * @dataProvider provideComponentPaths
   */
  public function testComponentComposerJson($component_path) {
    // Only copy the components. Copy all of them because some of them depend on
    // each other.
    $finder = $this->getCodebaseFinder();
    $finder->in($this->getDrupalRoot() . static::$componentsPath);
    $this->copyCodebase($finder->getIterator());

    $working_dir = $this->getWorkingPath() . static::$componentsPath . $component_path;

    // We add path repositories so we can wire internal dependencies together.
    $this->addExpectedRepositories($working_dir);

    // Perform the installation.
    $this->executeCommand("composer install --working-dir=$working_dir --no-interaction --no-progress");
    $this->assertCommandSuccessful();
  }

  protected function addExpectedRepositories($working_dir) {
    $repo_paths = [
      'Render' => 'drupal/core-render',
      'Utility' => 'drupal/core-utility',
    ];
    foreach ($repo_paths as $path => $package_name) {
      $path_repo = $this->getWorkingPath() . static::$componentsPath . '/' . $path;
      $repo_name = strtolower($path);
      // Add path repositories with the current version number to the current
      // package under test.
      $drupal_version = Composer::drupalVersionBranch();
      $this->executeCommand("composer config repositories.$repo_name " .
        "'{\"type\": \"path\",\"url\": \"$path_repo\",\"options\": {\"versions\": {\"$package_name\": \"$drupal_version\"}}}' --working-dir=$working_dir");
    }
  }

}
