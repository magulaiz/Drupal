<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\Core\Cache\Cache;
use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

/**
 * Tests demo_umami profile performance.
 *
 * @group OpenTelemetry
 * @group #slow
 * @requires extension apcu
 */
class OpenTelemetryPerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Tests performance of the Umami demo front page.
   */
  public function testPerformance(): void {
    $this->testNodePageCoolCache();
    $this->testNodePageWarmCache();
    $this->testNodePageHotCache();
    $this->testNodePageColdCache();
    $this->testFrontPageColdCache();
    $this->testFrontPageCoolCache();
    $this->testFrontPageHotCache();
    $this->testFrontPageAuthenticatedWarmCache();
  }

  /**
   * Logs node page tracing data with a cold cache.
   */
  protected function testNodePageColdCache(): void {
    $this->rebuildAll();
    sleep(2);
    $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageColdCache');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Logs node page tracing data with a hot cache.
   *
   * Hot here means that all possible caches are warmed.
   */
  protected function testNodePageHotCache(): void {
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('node/1');
    $this->drupalGet('node/1');

    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageHotCache');
    $this->assertSession()->pageTextContains('quiche');
    $this->assertSame($performance_data->getQueryCount(), 0);
    $this->assertSame($performance_data->getCacheGetCount(), 1);
    $this->assertSame($performance_data->getCacheSetCount(), 0);
    $this->assertSame($performance_data->getCacheDeleteCount(), 0);
    $this->assertSame(0, $performance_data->getCacheTagChecksumCount());
    $this->assertSame(1, $performance_data->getCacheTagIsValidCount());
  }

  /**
   * Logs node/1 tracing data with a cool cache.
   *
   * Cool here means that 'global' site caches are warm but anything
   * specific to the route or path is cold.
   */
  protected function testNodePageCoolCache(): void {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->clearCaches();
    // Now visit a non-node page to warm non-route-specific caches.
    $this->drupalGet('user/login');
    $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageCoolCache');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node/1 tracing data with a warm cache.
   *
   * Warm here means that 'global' site caches and route-specific caches are
   * warm but caches specific to this particular node/path are not.
   */
  protected function testNodePageWarmCache(): void {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->clearCaches();
    // Now visit a different node page to warm non-path-specific caches.
    $this->drupalGet('node/2');
    $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageWarmCache');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Logs front page tracing data with a cold cache.
   */
  protected function testFrontPageColdCache(): void {
    $this->rebuildAll();
    sleep(2);
    $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'umamiFrontPageColdCache');
    $this->assertSession()->pageTextContains('Umami');
  }

  /**
   * Logs front page tracing data with a hot cache.
   *
   * Hot here means that all possible caches are warmed.
   */
  protected function testFrontPageHotCache(): void {
    // Request the page twice so that asset aggregates and image derivatives are
    // definitely cached in the browser cache. The first response builds the
    // file and serves from PHP with private, no-store headers. The second
    // request will get the file served directly from disk by the browser with
    // cacheable headers, so only the third request actually has the files
    // in the browser cache.
    $this->drupalGet('<front>');
    $this->drupalGet('<front>');
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'umamiFrontPageHotCache');
    $this->assertSession()->pageTextContains('Umami');

    $expected_queries = [];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(0, $performance_data->getQueryCount());
    $this->assertSame(1, $performance_data->getCacheGetCount());
    $this->assertSame(0, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
    $this->assertSame(0, $performance_data->getCacheTagChecksumCount());
    $this->assertSame(1, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertLessThan(7500, $performance_data->getScriptBytes());
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertLessThan(40400, $performance_data->getStylesheetBytes());
  }

  /**
   * Logs front page tracing data with a lukewarm cache.
   *
   * Cool here means that 'global' site caches are warm but anything
   * specific to the front page is cold.
   */
  protected function testFrontPageCoolCache(): void {
    // First of all visit the front page to ensure the image style exists.
    $this->drupalGet('<front>');
    $this->clearCaches();
    // Now visit a different page to warm non-route-specific caches.
    $this->drupalGet('user/login');
    $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'umamiFrontPageCoolCache');
  }

  /**
   * Logs front page tracing data with an authenticated user and warm cache.
   */
  protected function testFrontPageAuthenticatedWarmCache(): void {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);
    $this->drupalGet('<front>');
    $this->drupalGet('<front>');

    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'authenticatedFrontPage');
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertLessThan(44000, $performance_data->getStylesheetBytes());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertLessThan(125000, $performance_data->getScriptBytes());

    $expected_queries = [
      'SELECT "session" FROM "sessions" WHERE "sid" = "SESSION_ID" LIMIT 0, 1',
      'SELECT * FROM "users_field_data" "u" WHERE "u"."uid" = "10" AND "u"."default_langcode" = 1',
      'SELECT "roles_target_id" FROM "user__roles" WHERE "entity_id" = "10"',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "language.entity.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(4, $performance_data->getQueryCount());
    $this->assertSame(43, $performance_data->getCacheGetCount());
    $this->assertSame(0, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
    $this->assertSame(0, $performance_data->getCacheTagChecksumCount());
    $this->assertSame(11, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
  }

  /**
   * Clear caches.
   */
  protected function clearCaches(): void {
    foreach (Cache::getBins() as $bin) {
      $bin->deleteAll();
    }
  }

}
