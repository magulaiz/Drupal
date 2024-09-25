<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;
/*

 */

/**
 * Tests the performance of the admin/content page in the Umami profile.
 *
 * @group OpenTelemetry
 * @group #slow
 */
class OpenTelemetryAdminContentPerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->drupalCreateUser(['access content overview']);
    $this->drupalLogin($user);
  }

  /**
   * Logs admin/content tracing data with a cold cache.
   */
  public function testAdminContentColdCache(): void {
    $this->rebuildAll();
    $performance_data = $this->collectPerformanceData(fn () => $this->drupalGet('/admin/content'), 'umamiAdminContentColdCache');

    // Check that the page contains the content overview.
    $this->assertSession()->elementExists('xpath', '//form[@id="views-form-content-page-1"]');

    // Check the performance data. Checking approximate values so we don't fail
    // too easily on minor changes.
    $this->assertValueInRange(550, 600, $performance_data->getQueryCount());
    $this->assertValueInRange(600, 650, $performance_data->getCacheGetCount());
    $this->assertValueInRange(425, 475, $performance_data->getCacheSetCount());
    $this->assertSame(2, $performance_data->getCacheDeleteCount());
    $this->assertValueInRange(250, 300, $performance_data->getCacheTagChecksumCount());
    $this->assertValueInRange(75, 100, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertValueInRange(70000, 75000, $performance_data->getStylesheetBytes());
    $this->assertValueInRange(240000, 250000, $performance_data->getScriptBytes());
  }

  /**
   * Logs admin/content tracing data with a hot cache.
   *
   * Hot here means that all possible caches are warmed.
   */
  public function _testNodePageHotCache() {
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('node/1');
    $this->drupalGet('node/1');
    $this->collectPerformanceData(function () {
      $this->drupalGet('/node/1');
    }, 'umamiNodePageHotCache');
    $exported_data = var_export($performance_data, TRUE);
    file_put_contents('umamiAdminContentColdCache.json', $exported_data);
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Logs node/1 tracing data with a cool cache.
   *
   * Cool here means that 'global' site caches are warm but anything
   * specific to the route or path is cold.
   */
  public function _testNodePageCoolCache() {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->rebuildAll();
    // Now visit a non-node page to warm non-route-specific caches.
    $this->drupalGet('/user/login');
    $this->collectPerformanceData(function () {
      $this->drupalGet('/node/1');
    }, 'umamiNodePageCoolCache');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node/1 tracing data with a warm cache.
   *
   * Warm here means that 'global' site caches and route-specific caches are
   * warm but caches specific to this particular node/path are not.
   */
  public function _testNodePageWarmCache() {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->rebuildAll();
    // Now visit a different node page to warm non-path-specific caches.
    $this->drupalGet('/node/2');
    $this->collectPerformanceData(function () {
      $this->drupalGet('/node/1');
    }, 'umamiNodePageWarmCache');
    $this->assertSession()->pageTextContains('quiche');
  }

}
