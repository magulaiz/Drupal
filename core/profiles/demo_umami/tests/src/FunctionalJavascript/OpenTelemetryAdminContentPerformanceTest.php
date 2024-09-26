<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

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
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('admin/content');
    // Wait a moment to ensure all assets have been generated.
    sleep(2);
    $this->drupalGet('admin/content');

    // Ensure the cache is cold. Not calling $this->rebuildAll() because we want
    // to avoid aggregated CSS/JS assets from being purged.
    $this->clearCaches();

    $performance_data = $this->collectPerformanceData(fn () => $this->drupalGet('admin/content'), 'umamiAdminContentColdCache');

    // Check that the page contains the content overview.
    $this->assertSession()->elementExists('xpath', '//form[@id="views-form-content-page-1"]');

    // Check the performance data. Checking approximate values so we don't fail
    // too easily on minor changes.
    $this->assertCountBetween(475, 500, $performance_data->getQueryCount());
    $this->assertCountBetween(560, 590, $performance_data->getCacheGetCount());
    $this->assertCountBetween(485, 510, $performance_data->getCacheSetCount());
    $this->assertEquals(0, $performance_data->getCacheDeleteCount());
    $this->assertCountBetween(250, 300, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(30, 70, $performance_data->getCacheTagIsValidCount());
    $this->assertEquals(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertEquals(2, $performance_data->getStylesheetCount());
    $this->assertEquals(1, $performance_data->getScriptCount());
    $this->assertCountBetween(70000, 75000, $performance_data->getStylesheetBytes());
    $this->assertCountBetween(240000, 250000, $performance_data->getScriptBytes());
  }

  /**
   * Logs admin/content tracing data with a warm cache.
   */
  public function testAdminContentWarmCache(): void {
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('admin/content');
    // Wait a moment to ensure all assets have been generated.
    sleep(2);
    $this->drupalGet('admin/content');
    $performance_data = $this->collectPerformanceData(fn () => $this->drupalGet('admin/content'), 'umamiAdminContentHotCache');

    // Check that the page contains the content overview.
    $this->assertSession()->elementExists('xpath', '//form[@id="views-form-content-page-1"]');

    // Check the performance data.
    $expected_queries = [
      'SELECT "session" FROM "sessions" WHERE "sid" = "SESSION_ID" LIMIT 0, 1',
      'SELECT * FROM "users_field_data" "u" WHERE "u"."uid" = "10" AND "u"."default_langcode" = 1',
      'SELECT "roles_target_id" FROM "user__roles" WHERE "entity_id" = "10"',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "language.entity.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "system.action.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "theme:umami" ) AND "collection" = "config.entity.key_store.block"',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);

    $this->assertEquals(6, $performance_data->getQueryCount());
    $this->assertEquals(235, $performance_data->getCacheGetCount());
    $this->assertEquals(0, $performance_data->getCacheSetCount());
    $this->assertEquals(0, $performance_data->getCacheDeleteCount());
    $this->assertEquals(0, $performance_data->getCacheTagChecksumCount());
    $this->assertEquals(120, $performance_data->getCacheTagIsValidCount());
    $this->assertEquals(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertEquals(2, $performance_data->getStylesheetCount());
    $this->assertEquals(1, $performance_data->getScriptCount());
    $this->assertCountBetween(70000, 75000, $performance_data->getStylesheetBytes());
    $this->assertCountBetween(240000, 250000, $performance_data->getScriptBytes());
  }

}
