<?php

namespace Drupal\Tests\update\Kernel;

use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

/**
 * Tests the releases stored in update_calculate_project_data() function.
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
    $this->setCoreVersion('9.8.0');
    $this->setReleaseMetadata(__DIR__ . '/../../fixtures/release-history/drupal.sec.0.2.xml');

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
   * Tests the releases stored.
   */
  public function testStoredReleases() {
    update_storage_clear();
    $available = update_get_available(TRUE);
    $new = update_calculate_project_data($available);
    self::assertNotEmpty($new['drupal']['releases']);
    self::assertArrayHasKey('8.0.2',$new['drupal']['releases']);
    self::assertArrayNotHasKey('8.0.1',$new['drupal']['releases']);
    self::assertArrayNotHasKey('8.0.0',$new['drupal']['releases']);
  }
}
