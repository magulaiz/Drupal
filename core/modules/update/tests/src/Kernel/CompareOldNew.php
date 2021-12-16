<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\Component\Render\MarkupInterface;
use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

/**
 * Just a temporary test prove new method produces exact same as old.
 *
 * @group update
 */
class CompareOldNew extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'update', 'update_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // The Update module's default configuration must be installed for our
    // fake release metadata to be fetched.
    $this->installConfig('update');
  }

  /**
   * Sets the current (running) version of core, as known to the Update module.
   *
   * @param string $version
   *   The current version of core.
   */
  protected function setCoreVersion(string $version): void {
    $this->config('update_test.settings')
      ->set('system_info.#all.version', $version)
      ->save();
  }

  /**
   * Sets the release metadata file to use when fetching available updates.
   *
   * @param string $file
   *   The path of the XML metadata file to use.
   */
  protected function setReleaseMetadata(string $file): void {
    $metadata = Utils::tryFopen($file, 'r');
    $response = new Response(200, [], Utils::streamFor($metadata));
    $handler = new MockHandler([$response]);
    $this->client = new Client([
      'handler' => HandlerStack::create($handler),
    ]);
    $this->container->set('http_client', $this->client);
  }

  public function testNewOld() {
    $core_versions = static::getAllPreviousCoreVersions('9.9.11');
    $core_files = $this->getCoreFixtures();
    $this->container->get('module_handler')->loadInclude('update', 'compare.inc');
    $this->container->get('module_handler')->loadInclude('update', 'compare-9.4.inc');
    $non_empty_releases_cnt = 0;

    foreach ($core_versions as $core_version) {
      foreach ($core_files as $core_file) {
        $this->setCoreVersion($core_version);
        $this->setReleaseMetadata($core_file);
        update_storage_clear();
        $available = update_get_available(TRUE);

        $new = update_calculate_project_data($available);
        if (!empty($new['drupal']['releases'])) {
          $non_empty_releases_cnt++;
        }
        $this->convert($new);
        self::assertNotEmpty($new['drupal']);
        $old = update_calculate_project_data_9_4($available);

        $this->convert($old);
        self::assertSame($new, $old, "core_version $core_version - file " . basename($core_file));

      }
    }
    self::assertGreaterThan(10, $non_empty_releases_cnt);

  }

  /**
   * Gets all the versions of Drupal 8 before a specific version.
   *
   * Copied from
   * \Drupal\Core\Extension\InfoParserDynamic::getAllPreviousCoreVersions().
   *
   * @param string $version
   *   The version to get versions before.
   *
   * @return array
   *   All of the applicable Drupal 8 releases.
   */
  protected static function getAllPreviousCoreVersions(string $version) {
    static $versions_lists = [];
    // Check if list of previous versions for the specified version has already
    // been created.
    if (empty($versions_lists[$version])) {
      foreach (range(8, 9) as $major) {
        // drupal core test don't use numbers of 9 in fixtures. test 10 to be 1
        // over
        foreach (range(0, 10) as $minor) {
          // The largest patch number in a release was 17 in 8.6.17. Use 27 to
          // leave room for future security releases.
          foreach (range(0, 11) as $patch) {
            $patch_version = "$major.$minor.$patch";
            if ($patch_version === $version) {
              // Reverse the order of the versions so that they will be evaluated
              // from the most recent versions first.
              $versions_lists[$version] = array_reverse($versions_lists[$version]);
              return $versions_lists[$version];
            }
            if ($patch === 0) {
              foreach (['alpha', 'beta', 'rc'] as $prerelease) {
                foreach (range(1, 3) as $prerelease_number) {
                  $versions_lists[$version][] = "$patch_version-$prerelease$prerelease_number";
                }
              }
            }
            $versions_lists[$version][] = $patch_version;
          }
        }
      }
    }

    return $versions_lists[$version];
  }

  private function getCoreFixtures() {
    $path = realpath(__DIR__ . '/../../fixtures/release-history');
    return glob($path . '/drupal.*.xml');

  }

  /**
   * Converts objects that will fail ::assertSame().
   *
   * @param array $data
   *   The data to convert.
   */
  private function convert(array &$data) {
    if (is_array($data)) {
      foreach ($data as &$datum) {
        if ($datum instanceof MarkupInterface) {
          $datum = $datum->__toString();
        }
        elseif (is_array($datum)) {
          $this->convert($datum);
        }
      }
    }
  }

}
