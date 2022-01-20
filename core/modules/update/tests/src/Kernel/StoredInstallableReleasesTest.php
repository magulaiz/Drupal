<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\update\UpdateManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

/**
 * Tests the releases stored in update_calculate_project_data().
 *
 * @group update
 */
class StoredInstallableReleasesTest extends KernelTestBase {

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
    $this->installConfig('update_test');
    $this->setReleaseMetadata(__DIR__ . '/../../fixtures/release-history/drupal_8.2_8.1_8.0.xml');

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

  /**
   * Provides expected installable releases with a specific installed version.
   *
   * All installed versions are from the xml except '8.1.4', to test for the
   * case if a release is not present in the xml.
   *
   * @return array[]
   *   Test data.
   */
  public function installableReleaseProvider(): array {
    return [
      'Installed version as 8.0.0' => [
        'installed_version' => '8.0.0',
        'expected_project_status' => UpdateManagerInterface::NOT_SECURE,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
          '8.0.0',
        ],
      ],
      'Installed version as 8.0.1' => [
        'installed_version' => '8.0.1',
        'expected_project_status' => UpdateManagerInterface::NOT_SUPPORTED,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
          '8.0.1',
        ],
      ],
      'Installed version as 8.0.2' => [
        'installed_version' => '8.0.2',
        'expected_project_status' => UpdateManagerInterface::NOT_SUPPORTED,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
          '8.0.2',
        ],
      ],
      'Installed version as 8.1.0' => [
        'installed_version' => '8.1.0',
        'expected_project_status' => UpdateManagerInterface::NOT_SECURE,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
          '8.1.0',
        ],
      ],
      'Installed version as 8.1.1' => [
        'installed_version' => '8.1.1',
        'expected_project_status' => UpdateManagerInterface::NOT_CURRENT,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
        ],
      ],
      'Installed version as 8.1.2' => [
        'installed_version' => '8.1.2',
        'expected_project_status' => UpdateManagerInterface::REVOKED,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.2',
        ],
      ],
      'Installed version as 8.1.3' => [
        'installed_version' => '8.1.3',
        'expected_project_status' => UpdateManagerInterface::NOT_CURRENT,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
        ],
      ],
      'Installed version as 8.1.4' => [
        'installed_version' => '8.1.4',
        'expected_project_status' => UpdateManagerInterface::NOT_CURRENT,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.1.3',
          '8.1.1',
        ],
      ],
      'Installed version as 8.2.0' => [
        'installed_version' => '8.2.0',
        'expected_project_status' => UpdateManagerInterface::NOT_SECURE,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
          '8.2.0',
        ],
      ],
      'Installed version as 8.2.1' => [
        'installed_version' => '8.2.1',
        'expected_project_status' => UpdateManagerInterface::NOT_CURRENT,
        'expected_releases' => [
          '8.2.3',
          '8.2.1',
        ],
      ],
      'Installed version as 8.2.2' => [
        'installed_version' => '8.2.2',
        'expected_project_status' => UpdateManagerInterface::REVOKED,
        'expected_releases' => [
          '8.2.3',
          '8.2.2',
        ],
      ],
      'Installed version as 8.2.3' => [
        'installed_version' => '8.2.3',
        'expected_project_status' => UpdateManagerInterface::CURRENT,
        'expected_releases' => [
          '8.2.3',
        ],
      ],
    ];
  }

  /**
   * Tests the releases are stored.
   *
   * @dataProvider installableReleaseProvider
   */
  public function testStoredReleases($installed_version, $expected_project_status, $expected_releases): void {
    $this->setCoreVersion($installed_version);
    update_storage_clear();
    $available = update_get_available(TRUE);
    $project_data = update_calculate_project_data($available);
    $this->assertEquals($project_data['drupal']['status'], $expected_project_status);
    $this->assertSame($expected_releases, array_keys($project_data['drupal']['releases']));
    foreach ($expected_releases as $version) {
      $this->assertArrayHasKey($version, $project_data['drupal']['releases']);
      if ($expected_project_status === UpdateManagerInterface::REVOKED && $project_data['drupal']['releases'][$version]['version'] === $installed_version) {
        $this->assertEquals($project_data['drupal']['releases'][$version]['status'], 'unpublished');
      }
      else {
        $this->assertEquals($project_data['drupal']['releases'][$version]['status'], 'published');
      }
      $this->assertEquals($project_data['drupal']['releases'][$version]['version'], $version);
    }
  }

}
