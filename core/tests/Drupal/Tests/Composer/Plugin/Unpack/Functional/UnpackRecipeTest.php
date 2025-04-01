<?php

declare(strict_types=1);

namespace Drupal\Tests\Composer\Plugin\Unpack\Functional;

use Composer\Util\Filesystem;
use Drupal\Tests\Composer\Plugin\Unpack\Fixtures;
use Drupal\BuildTests\Framework\BuildTestBase;
use Drupal\Tests\Composer\Plugin\Scaffold\ExecTrait;

/**
 * Tests recipe unpacking.
 *
 * @group Unpack
 */
class UnpackRecipeTest extends BuildTestBase {

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
   * @var \Composer\Util\Filesystem
   */
  protected Filesystem $fileSystem;

  /**
   * The Fixtures object.
   *
   * @var \Drupal\Tests\Composer\Plugin\Unpack\Fixtures
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
    // Remove any temporary directories that were created.
    $this->fixtures->tearDown();
    parent::tearDown();
  }

  /**
   * Tests that only recipes can be unpacked.
   */
  public function testOnlyRecipesCanBeUnpacked(): void {
    $this->markTestSkipped('Not implemented yet.');
  }

  /**
   * Tests that the unpacking process will leave extant constraints alone.
   */
  public function testUnpackingRespectsExistingConstraints(): void {
    $this->markTestSkipped('Not implemented yet.');
  }

  /**
   * Tests the dependencies unpack on install.
   */
  public function testAutomaticUnpack(): void {
    $root_project_dir = 'composer-root';
    $root_project_path = $this->fixturesDir . '/' . $root_project_dir;

    // Run composer install and confirm the composer.lock was created.
    $this->mustExec('composer install --no-ansi', $root_project_path);
    $this->assertFileExists("$root_project_path/composer.lock");

    // Install a module in require-dev that should be moved to require.
    $this->mustExec('composer require --dev --no-ansi --no-interaction fixtures/module-a', $root_project_path);
    // Ensure we have added the dependency to require-dev.
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $this->assertArrayHasKey('fixtures/module-a', $root_composer_json['require-dev']);

    // Install a recipe and unpack it.
    $stdout = $this->mustExec('composer require --no-ansi --no-interaction fixtures/recipe-a', $root_project_path);
    $this->doTestRecipeAUnpacked($root_project_path, $stdout);
  }

  /**
   * Tests the dependencies unpack on dev install.
   */
  public function testAutomaticDevUnpack(): void {
    $root_project_dir = 'composer-root';
    $root_project_path = $this->fixturesDir . '/' . $root_project_dir;

    // Run composer install and confirm the composer.lock was created.
    $this->mustExec('composer install --no-ansi', $root_project_path);
    $this->assertFileExists("$root_project_path/composer.lock");

    // Install a module in require that should be moved to require-dev.
    $this->mustExec('composer require --no-ansi --no-interaction fixtures/module-a', $root_project_path);
    // Ensure we have added the dependency to require-dev
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $this->assertArrayHasKey('fixtures/module-a', $root_composer_json['require']);

    // Install a recipe and unpack it.
    $stdout = $this->mustExec('composer require --dev --no-ansi --no-interaction fixtures/recipe-a', $root_project_path);

    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $root_composer_lock = $this->getFileContents($root_project_path . '/composer.lock');
    dump($root_composer_json);

    $expected_unpacked = $this->dependenciesData();
    foreach ($expected_unpacked as $package => $dependencies) {
      $package_type = $this->getPackageType($package);
      $this->assertNotNull($package_type);

      // When the package is unpacked, the unpacked dependencies should be logged
      // in the stdout.
      $this->assertStringContainsString("The $package recipe was unpacked successfully.", $stdout);

      // After being unpacked, the package should be removed from the root
      // composer.json and composer.lock.
      $this->assertArrayNotHasKey($package, $root_composer_json['require-dev']);
      $this->assertNotTrue($this->isPackageInComposerLock($package, $root_composer_lock));

      foreach ($dependencies as $dependency) {
        $key = $dependency === 'fixtures/module-a' ? 'require' : 'require-dev';
        // The package dependencies should be in the root composer.json.
        $this->assertArrayHasKey($dependency, $root_composer_json[$key]);
      }
    }

    // The dependency has not moved to require-dev.
    $this->assertArrayNotHasKey('fixtures/module-a', $root_composer_json['require-dev']);
  }

  /**
   * Tests the dependencies unpack on using drupal:unpack.
   */
  public function testUnpackCommand(): void {
    $root_project_dir = 'composer-root';
    $root_project_path = $this->fixturesDir . '/' . $root_project_dir;

    // Run composer install and confirm the composer.lock was created.
    $this->mustExec('composer install --no-ansi', $root_project_path);
    $this->assertFileExists("$root_project_path/composer.lock");

    // Disable automatic unpacking as it is the default behavior,
    $this->mustExec('composer config --merge --json extra.drupal-core-composer-unpack.on-install-and-update false', $root_project_path);

    // Install a module in require-dev that should be moved to require on
    // unpacking.
    $this->mustExec('composer require --dev --no-ansi --no-interaction fixtures/module-a', $root_project_path);
    // Ensure we have added the dependency to require-dev
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $this->assertArrayHasKey('fixtures/module-a', $root_composer_json['require-dev']);

    // Install a recipe and check it is not unpacked.
    $stdout = $this->mustExec('composer require --no-ansi --no-interaction fixtures/recipe-a', $root_project_path);
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $root_composer_lock = $this->getFileContents($root_project_path . '/composer.lock');

    $package_type = $this->getPackageType('fixtures/recipe-a');
    $this->assertNotNull($package_type);

    // When the package is unpacked, the unpacked dependencies should be logged
    // in the stdout.
    $this->assertStringNotContainsString("unpacked successfully.", $stdout);

    $this->assertArrayHasKey('fixtures/recipe-a', $root_composer_json['require']);
    $this->assertTrue($this->isPackageInComposerLock('fixtures/recipe-a', $root_composer_lock));

    // The package dependencies should not be in the root composer.json.
    $this->assertArrayNotHasKey('fixtures/recipe-b', $root_composer_json['require']);

    // The dev dependency has not moved.
    $this->assertArrayHasKey('fixtures/module-a', $root_composer_json['require-dev']);

    $stdout = $this->mustExec('composer drupal:unpack fixtures/recipe-a', $root_project_path);
    $this->doTestRecipeAUnpacked($root_project_path, $stdout);
  }

  /**
   * Tests Recipe A is unpacked correctly.
   *
   * @param string $root_project_path
   *   Path to the composer project under test.
   * @param string $stdout
   *   The standard out from the composer command unpacks the recipe.
   */
  private function doTestRecipeAUnpacked(string $root_project_path, string $stdout): void {
    $root_composer_json = $this->getFileContents($root_project_path . '/composer.json');
    $root_composer_lock = $this->getFileContents($root_project_path . '/composer.lock');

    $expected_unpacked = $this->dependenciesData();
    foreach ($expected_unpacked as $package => $dependencies) {
      $package_type = $this->getPackageType($package);
      $this->assertNotNull($package_type);

      // When the package is unpacked, the unpacked dependencies should be logged
      // in the stdout.
      $this->assertStringContainsString("The $package recipe was unpacked successfully.", $stdout);

      // After being unpacked, the package should be removed from the root
      // composer.json and composer.lock.
      $this->assertArrayNotHasKey($package, $root_composer_json['require']);
      $this->assertNotTrue($this->isPackageInComposerLock($package, $root_composer_lock));

      foreach ($dependencies as $dependency) {
        // The package dependencies should be in the root composer.json.
        $this->assertArrayHasKey($dependency, $root_composer_json['require']);
      }
    }

    // The dev dependency has moved.
    $this->assertArrayNotHasKey('require-dev', $root_composer_json);
  }

  /**
   * Gets the packages dependencies that should be unpacked.
   *
   * @return array<string, array<string>>
   *   The packages that need to be unpacked and their dependencies.
   */
  private function dependenciesData(): array {
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
   * Gets the contents of a file as an array.
   *
   * @param string $path
   *   The path to the file.
   *
   * @return array
   *   The contents of the file as an array.
   */
  private function getFileContents(string $path): array {
    $file = file_get_contents($path);
    return json_decode($file, TRUE, flags: JSON_THROW_ON_ERROR);
  }

  /**
   * Gets the package type from the package name.
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
  private function getPackageType(string $package_name): ?string {
    if (preg_match('/fixtures\/(\w+)-/', $package_name, $matches)) {
      return 'drupal-' . $matches[1];
    }
    return NULL;
  }

  /**
   * Checks if a package is in the composer.lock.
   *
   * @param string $package_name
   *   The package name.
   * @param array $composer_lock
   *   The composer.lock.
   *
   * @return bool
   *   TRUE if the package is in the composer.lock, FALSE otherwise.
   */
  private function isPackageInComposerLock(string $package_name, array $composer_lock): bool {
    if (!isset($composer_lock['packages'])) {
      return FALSE;
    }

    foreach ($composer_lock['packages'] as $package) {
      if (isset($package['name']) && $package['name'] === $package_name) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
