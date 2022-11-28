<?php

namespace Drupal\Tests;

use Drupal\Composer\Plugin\VendorHardening\Config;
use Drupal\Tests\Composer\ComposerIntegrationTrait;
use Symfony\Component\Finder\Finder;

/**
 * Tests Composer integration.
 *
 * @group Composer
 */
class ComposerIntegrationTest extends UnitTestCase {

  use ComposerIntegrationTrait;

  /**
   * Tests composer.lock content-hash.
   *
   * If you have made a change to composer.json, you may need to reconstruct
   * composer.lock. Follow the link below for further instructions.
   *
   * @see https://www.drupal.org/about/core/policies/core-dependencies-policies/managing-composer-updates-for-drupal-core
   */
  public function testComposerLockHash() {
    $content_hash = self::getContentHash(file_get_contents($this->root . '/composer.json'));
    $lock = json_decode(file_get_contents($this->root . '/composer.lock'), TRUE);
    $this->assertSame($content_hash, $lock['content-hash']);

    // @see \Composer\Repository\PathRepository::initialize()
    $core_lock_file_hash = '';
    $options = [];
    foreach ($lock['packages'] as $package) {
      if ($package['name'] === 'drupal/core') {
        $core_lock_file_hash = $package['dist']['reference'];
        $options = $package['transport-options'] ?? [];
        break;
      }
    }
    $core_content_hash = sha1(file_get_contents($this->root . '/core/composer.json') . serialize($options));
    $this->assertSame($core_content_hash, $core_lock_file_hash);
  }

  /**
   * Tests composer.json versions.
   *
   * @param string $path
   *   Path to a composer.json to test.
   *
   * @dataProvider providerTestComposerJson
   */
  public function testComposerTilde($path) {
    if (preg_match('#composer/Metapackage/CoreRecommended/composer.json$#', $path)) {
      $this->markTestSkipped("$path has tilde");
    }
    $content = json_decode(file_get_contents($path), TRUE);
    $composer_keys = array_intersect(['require', 'require-dev'], array_keys($content));
    if (empty($composer_keys)) {
      $this->markTestSkipped("$path has no keys to test");
    }
    foreach ($composer_keys as $composer_key) {
      foreach ($content[$composer_key] as $dependency => $version) {
        // We allow tildes if the dependency is a Symfony component.
        // @see https://www.drupal.org/node/2887000
        if (strpos($dependency, 'symfony/') === 0) {
          continue;
        }
        $this->assertStringNotContainsString('~', $version, "Dependency $dependency in $path contains a tilde, use a caret.");
      }
    }
  }

  /**
   * Data provider for all the composer.json provided by Drupal core.
   *
   * @return array
   */
  public function providerTestComposerJson() {
    $data = [];
    $composer_json_finder = $this->getComposerJsonFinder(realpath(__DIR__ . '/../../../../'));
    foreach ($composer_json_finder->getIterator() as $composer_json) {
      $data[$composer_json->getPathname()] = [$composer_json->getPathname()];
    }
    return $data;
  }

  /**
   * Tests core's composer.json replace section.
   *
   * Verify that all core components are also listed in the 'replace' section of
   * core's composer.json.
   */
  public function testAllCoreComponentsReplaced(): void {
    // Assemble a path to core components.
    $components_path = $this->root . '/core/lib/Drupal/Component';

    // Grab the 'replace' section of the core composer.json file.
    $json = json_decode(file_get_contents($this->root . '/core/composer.json'), FALSE);
    $composer_replace_packages = (array) $json->replace;

    // Get a list of all the composer.json files in the components path.
    $components_composer_json_files = [];

    $composer_json_finder = new Finder();
    $composer_json_finder->name('composer.json')
      ->in($components_path)
      ->ignoreUnreadableDirs();

    foreach ($composer_json_finder->getIterator() as $composer_json) {
      $components_composer_json_files[$composer_json->getPathname()] = [$composer_json->getPathname()];
    }

    $this->assertNotEmpty($components_composer_json_files);
    $this->assertCount(count($composer_replace_packages), $components_composer_json_files);

    // Assert that each core components has a corresponding 'replace' in
    // composer.json.
    foreach ($components_composer_json_files as $components_composer_json_file) {
      $json = json_decode(file_get_contents(reset($components_composer_json_file)), FALSE);
      $component_name = $json->name;

      $this->assertArrayHasKey(
        $component_name,
        $composer_replace_packages,
        'Unable to find ' . $component_name . ' in replace list of composer.json'
      );
    }
  }

  // phpcs:disable
  /**
   * The following method is copied from \Composer\Package\Locker.
   *
   * @see https://github.com/composer/composer
   */
  /**
   * Returns the md5 hash of the sorted content of the composer file.
   *
   * @param string $composerFileContents The contents of the composer file.
   *
   * @return string
   */
  protected static function getContentHash($composerFileContents)
  {
    $content = json_decode($composerFileContents, true);

    $relevantKeys = array(
      'name',
      'version',
      'require',
      'require-dev',
      'conflict',
      'replace',
      'provide',
      'minimum-stability',
      'prefer-stable',
      'repositories',
      'extra',
    );

    $relevantContent = array();

    foreach (array_intersect($relevantKeys, array_keys($content)) as $key) {
      $relevantContent[$key] = $content[$key];
    }
    if (isset($content['config']['platform'])) {
      $relevantContent['config']['platform'] = $content['config']['platform'];
    }

    ksort($relevantContent);

    return md5(json_encode($relevantContent));
  }
  // phpcs:enable

  /**
   * Tests the vendor cleanup utilities do not have obsolete packages listed.
   */
  public function testVendorCleanup(): void {
    $lock = json_decode(file_get_contents($this->root . '/composer.lock'), TRUE);
    $packages = [];
    foreach (array_merge($lock['packages'], $lock['packages-dev']) as $package) {
      $packages[] = $package['name'];
    }

    $reflection = new \ReflectionProperty(Config::class, 'defaultConfig');
    $reflection->setAccessible(TRUE);
    $config = $reflection->getValue();
    foreach (array_keys($config) as $package) {
      $this->assertContains(strtolower($package), $packages);
    }
  }

}
