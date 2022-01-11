<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\KernelTests\KernelTestBase;
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
    $this->setCoreVersion('8.1.1');
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
   * Tests the releases are stored.
   */
  public function testStoredReleases() {
    update_storage_clear();
    $available = update_get_available(TRUE);
    $new = update_calculate_project_data($available);
    $expected_versions = [
      '8.2.3',
      '8.2.1',
      '8.1.3',
      '8.1.1',
    ];
    $not_expected_versions = [
      '8.0.2',
      '8.0.1',
      '8.0.0',
      '8.1.2',
      '8.1.0',
      '8.2.2',
      '8.2.0',
    ];
    foreach ($expected_versions as $version){
      $this->assertArrayHasKey($version, $new['drupal']['releases']);
      $this->assertArrayHasKey('status', $new['drupal']['releases'][$version]);
      $this->assertArrayHasKey('version', $new['drupal']['releases'][$version]);
    }
    foreach ($not_expected_versions as $version){
      $this->assertArrayNotHasKey($version, $new['drupal']['releases']);
    }
  }

}
