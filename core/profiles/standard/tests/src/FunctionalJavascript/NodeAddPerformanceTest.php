<?php

declare(strict_types=1);

namespace Drupal\Tests\standard\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;
use Drupal\user\RoleInterface;

/**
 * Tests the performance of basic functionality in the standard profile.
 *
 * Stark is used as the default theme so that this test is not Olivero specific.
 *
 * @group Common
 * @group #slow
 * @requires extension apcu
 */
class NodeAddPerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    user_role_change_permissions(RoleInterface::ANONYMOUS_ID, [
      'create article content' => TRUE,
      'access content' => TRUE,
    ]);
    user_role_change_permissions(RoleInterface::AUTHENTICATED_ID, [
      'create article content' => TRUE,
      'access content' => TRUE,
    ]);
  }

  /**
   * Tests performance of the standard profile.
   */
  public function testPerformance(): void {
    $this->testColdCache();
    $this->testHotCache();
    $this->testWarmCache();
  }

  /**
   * Logs node add page tracing data with a cold cache.
   */
  protected function testColdCache(): void {
    // @todo Chromedriver doesn't collect tracing performance logs for the very
    //   first request in a test, so warm it up.
    //   https://www.drupal.org/project/drupal/issues/3379750
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/add/article');
    }, 'standardNodeAddPageColdCache');
    $this->assertSession()->pageTextContains('Create Article');

    // Only count queries.
    // We cannot compare queries as they are not deterministic.
    $this->assertSame(124, $performance_data->getQueryCount());
    $this->assertSame(227, $performance_data->getCacheGetCount());
    $this->assertSame(190, $performance_data->getCacheSetCount());
    $this->assertSame(1, $performance_data->getCacheDeleteCount());
    $this->assertCountBetween(30, 127, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(30, 39, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertCountBetween(212000, 213000, $performance_data->getScriptBytes());
    $this->assertSame(1, $performance_data->getStylesheetCount());
    $this->assertCountBetween(29500, 30500, $performance_data->getStylesheetBytes());
  }

  /**
   * Logs node add page tracing data with a hot cache.
   *
   * Hot here means that all possible caches are warmed.
   */
  protected function testHotCache(): void {
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('node/add/article');
    $this->drupalGet('node/add/article');

    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/add/article');
    }, 'standardNodeAddPageHotCache');
    $this->assertSession()->pageTextContains('Create Article');

    $recorded_queries = $performance_data->getQueries();
    $this->assertSame([], $recorded_queries);
    $this->assertSame(0, $performance_data->getQueryCount());
    $this->assertSame(1, $performance_data->getCacheGetCount());
    $this->assertSame(0, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
    $this->assertSame(0, $performance_data->getCacheTagChecksumCount());
    $this->assertSame(1, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertSame(212244, $performance_data->getScriptBytes());
    $this->assertSame(1, $performance_data->getStylesheetCount());
    $this->assertSame(29911, $performance_data->getStylesheetBytes());
  }

  /**
   * Logs front page tracing data with an authenticated user and warm cache.
   */
  protected function testWarmCache(): void {
    $user = $this->drupalCreateUser();
    $this->drupalLogin($user);

    $this->drupalGet('node/add/article');
    $this->drupalGet('node/add/article');

    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/add/article');
    }, 'standardNodeAddPageWarmCache');

    $expected_queries = [
      'SELECT "session" FROM "sessions" WHERE "sid" = "SESSION_ID" LIMIT 0, 1',
      'SELECT * FROM "users_field_data" "u" WHERE "u"."uid" = "2" AND "u"."default_langcode" = 1',
      'SELECT "roles_target_id" FROM "user__roles" WHERE "entity_id" = "2"',
      'SELECT 1 FROM "key_value" WHERE "collection" = "entity_autocomplete" AND "name" = ""',
      'SELECT 1 FROM "key_value" WHERE "collection" = "entity_autocomplete" AND "name" = ""',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "editor.editor.%" ESCAPE \'\\\\\') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "theme:stark" ) AND "collection" = "config.entity.key_store.block"',
    ];
    $recorded_queries = $performance_data->getQueries();

    // We need to replace queries that are not deterministic removing the random
    // part.
    $pattern = '/^SELECT 1 FROM "key_value" WHERE "collection" = "entity_autocomplete" AND "name" = "([^"]*)"/';
    $replacement = 'SELECT 1 FROM "key_value" WHERE "collection" = "entity_autocomplete" AND "name" = ""';
    $recorded_queries_cleaned = $this->cleanQueries($pattern, $replacement, $recorded_queries);
    $this->assertSame($expected_queries, $recorded_queries_cleaned);
    $this->assertSame(7, $performance_data->getQueryCount());
    $this->assertSame(85, $performance_data->getCacheGetCount());
    $this->assertSame(0, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
    $this->assertSame(0, $performance_data->getCacheTagChecksumCount());
    $this->assertSame(39, $performance_data->getCacheTagIsValidCount());
    $this->assertSame(0, $performance_data->getCacheTagInvalidationCount());
    $this->assertSame(3, $performance_data->getScriptCount());
    $this->assertCountBetween(1631000, 1632000, $performance_data->getScriptBytes());
    $this->assertSame(1, $performance_data->getStylesheetCount());
    $this->assertCountBetween(36500, 37500, $performance_data->getStylesheetBytes());
  }

  /**
   * Clean queries removing random part based on pattern and replacement.
   *
   * @param string $pattern
   *   The query pattern to search.
   * @param string $replacement
   *   The query replacement.
   * @param $queries
   *   The queries array.
   *
   * @return array
   *   The cleaned queries.
   */
  protected function cleanQueries(string $pattern, string $replacement, array $queries): array {
    foreach ($queries as &$query) {
      if (preg_match($pattern, $query)) {
        $query = preg_replace($pattern, $replacement, $query);
      }
    }

    return $queries;
  }

}
