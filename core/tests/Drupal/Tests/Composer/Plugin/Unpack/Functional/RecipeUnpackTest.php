<?php

declare(strict_types=1);

namespace Drupal\Tests\Composer\Plugin\Unpack\Functional;

use Composer\Util\Filesystem;
use Drupal\Tests\Composer\Plugin\Unpack\Fixtures;
use Drupal\BuildTests\Framework\BuildTestBase;
use Drupal\Tests\Composer\Plugin\Scaffold\ExecTrait;

/**
 * Tests recipe unpacking.
 */
class RecipeUnpackTest extends BuildTestBase {
  use ExecTrait;

  /**
   * Directory to perform the tests in.
   *
   * @var string
   */
  protected string $fixturesDir;

  /**
   * The Symfony FileSystem component.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected Filesystem $fileSystem;

  /**
   * The Fixtures object.
   *
   * @var Drupal\Tests\Composer\Plugin\Unpack\Fixtures
   */
  protected Fixtures $fixtures;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->fileSystem = new Filesystem();
    $this->fixtures = new Fixtures();
    $this->fixtures->createIsolatedComposerCacheDir();
    $this->fixturesDir = $this->fixtures->tmpDir($this->name());
    $replacements = ['PROJECT_ROOT' => $this->fixtures->projectRoot()];
    $this->fixtures->cloneFixtureProjects($this->fixturesDir, $replacements);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    // Remove any temporary directories et. al. that were created.
    $this->fixtures->tearDown();
    parent::tearDown();
  }

  /**
   * Tests the dependencies unpack on install.
   */
  public function testUnpack(): void {
    $root_project_dir = 'composer-root';
    $root_project_path = $this->fixturesDir . '/' . $root_project_dir;

    // Run composer install and confirm the composer.lock was created.
    $this->mustExec('composer install --no-ansi', $root_project_path);
    $this->assertFileExists("$root_project_path/composer.lock");

    // Install a module in require-dev that should be moved to require.
    $this->mustExec('composer require --dev --no-ansi --no-interaction fixtures/module-a', $root_project_path);
    // Ensure we have added a the dependency to require-dev
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $this->assertArrayHasKey('fixtures/module-a', $root_composer_json['require-dev']);

    // Install a recipe and unpack it.
    $stdout = $this->mustExec('composer require --no-ansi --no-interaction fixtures/recipe-a', $root_project_path);
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $root_composer_lock = $this->getFileContents($root_project_path . '/composer.lock');

    $this->assertIsArray($root_composer_json);
    $this->assertIsArray($root_composer_lock);

    $expected_unpacked = $this->dependenciesData();
    foreach ($expected_unpacked as $package => $dependencies) {
      $package_type = $this->getPackageType($package);
      $this->assertNotNull($package_type);

      // When the package is unpacked, the unpacked dependencies should be logged
      // in the stdout.
      $this->assertStringContainsString("Package $package of type $package_type was unpacked successfully.", $stdout);

      // After being unpacked, the package should be removed from the root
      // composer.json and composer.lock.
      $this->assertArrayNotHasKey($package, $root_composer_json['require']);
      $this->assertNotTrue($this->isPackageInComposerLock($package, 'require', $root_composer_lock));

      foreach ($dependencies as $dependency) {
        // The package dependencies should be in the root composer.json.
        $this->assertArrayHasKey($dependency, $root_composer_json['require']);
      }
      // The dev dependency has moved.
      $this->assertEmpty($root_composer_json['require-dev']);
    }
  }

  /**
   * The packages dependencies that should be unpacked.
   *
   * @return array<string, array<string>>
   *   The packages that need to be unpacked and their dependencies.
   */
  public function dependenciesData(): array {
    return [
      'fixtures/recipe-a' => [
        'fixtures/module-b',
      ],
      'fixtures/recipe-b' => [
        'fixtures/module-a',
        'fixtures/theme-a',
      ],
    ];
  }

  /**
   * Get the contents of a file as an array.
   *
   * @param string $path
   *   The path to the file.
   *
   * @return array
   *   The contents of the file as an array.
   */
  protected function getFileContents(string $path): array {
    $file = file_get_contents($path);
    $file_decoded = json_decode($file, TRUE);

    return $file_decoded;
  }

  /**
   * Get the package type from the package name.
   *
   * The package name should follow 'fixtures/[PACKAGE_TYPE]-[PACKAGE]' format.
   * For example, 'fixtures/recipe-a' will return 'drupal-recipe'.
   *
   * @param string $package_name
   *   The package name.
   *
   * @return string|null
   *   The package type.
   */
  protected function getPackageType(string $package_name): ?string {
    $type = NULL;
    if (preg_match('/fixtures\/(\w+)-/', $package_name, $matches)) {
      $type = 'drupal-' . $matches[1];
    }

    return $type;
  }

  /**
   * Check if a package is in the composer.lock.
   *
   * @param string $package_name
   *   The package name.
   * @param string $section
   *   The section of the composer.lock.
   * @param array $composer_lock
   *   The composer.lock.
   *
   * @return bool
   *   TRUE if the package is in the composer.lock, FALSE otherwise.
   */
  public function isPackageInComposerLock(string $package_name, string $section, array $composer_lock): bool {
    if (!isset($composer_lock[$section])) {
      return FALSE;
    }

    foreach ($composer_lock[$section] as $package) {
      if (isset($package['name']) && $package['name'] === $package_name) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
