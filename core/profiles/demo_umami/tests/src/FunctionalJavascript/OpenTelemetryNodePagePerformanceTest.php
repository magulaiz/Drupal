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
class OpenTelemetryNodePagePerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Test canonical node page performance with various cache permutations.
   */
  public function testNodePage(): void {
    $this->testNodePageColdCache();
    $this->testNodePageCoolCache();
    $this->testNodePageWarmCache();
    $this->testNodePageHotCache();
  }

  /**
   * Logs node page tracing data with a cold cache.
   */
  protected function testNodePageColdCache(): void {
    // @todo Chromedriver doesn't collect tracing performance logs for the very
    //   first request in a test, so warm it up.
    //   https://www.drupal.org/project/drupal/issues/3379750
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageColdCache');
    $this->assertSession()->pageTextContains('quiche');

    $this->assertCountBetween(230, 240, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(30, 80, $performance_data->getCacheTagIsValidCount());
    $this->assertCountBetween(420, 450, $performance_data->getQueryCount());
    $this->assertCountBetween(570, 610, $performance_data->getCacheGetCount());
    $this->assertCountBetween(390, 415, $performance_data->getCacheSetCount());
    $this->assertCountBetween(1, 2, $performance_data->getCacheDeleteCount());
    $this->assertCountBetween(52, 53, $performance_data->getCacheTagLookupQueryCount());
    $expected = [
      'CacheTagInvalidationCount' => 0,
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 43000,
    ];
    $this->assertMetrics($expected, $performance_data);
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

    $expected = [
      'QueryCount' => 0,
      'CacheGetCount' => 1,
      'CacheSetCount' => 0,
      'CacheDeleteCount' => 0,
      'CacheTagInvalidationCount' => 0,
      'CacheTagLookupQueryCount' => 1,
      'CacheTagChecksumCount' => 0,
      'CacheTagIsValidCount' => 1,
      'CacheTagGroupedLookups' => [
        ['CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form', 'block_content:1', 'block_content:2', 'block_content_view', 'block_view', 'config:block.block.umami_account_menu', 'config:block.block.umami_banner_home', 'config:block.block.umami_banner_recipes', 'config:block.block.umami_branding', 'config:block.block.umami_breadcrumbs', 'config:block.block.umami_content', 'config:block.block.umami_disclaimer', 'config:block.block.umami_footer', 'config:block.block.umami_footer_promo', 'config:block.block.umami_help', 'config:block.block.umami_languageswitcher', 'config:block.block.umami_local_tasks', 'config:block.block.umami_main_menu', 'config:block.block.umami_messages', 'config:block.block.umami_page_title', 'config:block.block.umami_search', 'config:block.block.umami_views_block__articles_aside_block_1', 'config:block.block.umami_views_block__promoted_items_block_1', 'config:block.block.umami_views_block__recipe_collections_block', 'config:block_list', 'config:configurable_language_list', 'config:core.entity_view_display.block_content.footer_promo_block.default', 'config:core.entity_view_display.media.image.medium_8_7', 'config:core.entity_view_display.media.image.responsive_3x2', 'config:core.entity_view_display.node.recipe.card', 'config:core.entity_view_display.node.recipe.full', 'config:filter.format.basic_html', 'config:image.style.large_3_2_2x', 'config:image.style.large_3_2_768x512', 'config:image.style.medium_3_2_2x', 'config:image.style.medium_3_2_600x400', 'config:image.style.medium_8_7', 'config:responsive_image.styles.3_2_image', 'config:system.menu.account', 'config:system.menu.footer', 'config:system.menu.main', 'config:system.site', 'config:user.role.anonymous', 'config:views.view.recipe_collections', 'config:views.view.related_recipes', 'config:workflows.workflow.editorial', 'file:37', 'http_response', 'local_task', 'media:1', 'media:19', 'media:21', 'media:3', 'media:6', 'media:7', 'media_view', 'node:1', 'node:10', 'node:3', 'node:6', 'node:7', 'node_list', 'node_view', 'rendered', 'taxonomy_term:1', 'taxonomy_term:10', 'taxonomy_term:11', 'taxonomy_term:12', 'taxonomy_term:13', 'taxonomy_term:14', 'taxonomy_term:15', 'taxonomy_term:16', 'taxonomy_term:2', 'taxonomy_term:22', 'taxonomy_term:3', 'taxonomy_term:31', 'taxonomy_term:4', 'taxonomy_term:5', 'taxonomy_term:6', 'taxonomy_term:7', 'taxonomy_term:8', 'taxonomy_term:9', 'taxonomy_term_list', 'user:6'],
      ],
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 43000,
    ];
    $this->assertMetrics($expected, $performance_data);
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
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageCoolCache');
    $this->assertSession()->pageTextContains('quiche');

    $this->assertCountBetween(72, 73, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(85, 86, $performance_data->getCacheTagIsValidCount());
    $expected = [
      'QueryCount' => 192,
      'CacheGetCount' => 255,
      'CacheSetCount' => 65,
      'CacheDeleteCount' => 0,
      'CacheTagInvalidationCount' => 0,
      'CacheTagLookupQueryCount' => 28,
      'CacheTagGroupedLookups' => [
        ['entity_types', 'route_match', 'access_policies', 'routes', 'router', 'entity_field_info', 'entity_bundles', 'local_task', 'library_info'],
        ['config:views.view.related_recipes'],
        ['config:core.extension', 'views_data'],
        ['node:10', 'node:3', 'node:6', 'node:7', 'node_list'],
        ['breakpoints'],
        ['config:core.entity_view_display.media.image.responsive_3x2', 'config:image.style.large_3_2_2x', 'config:image.style.large_3_2_768x512', 'config:image.style.medium_3_2_2x', 'config:image.style.medium_3_2_600x400', 'config:responsive_image.styles.3_2_image', 'media:1', 'media_view', 'rendered'],
        ['media:21'],
        ['config:core.entity_view_display.node.recipe.card', 'node_view', 'user:6'],
        ['media:7'],
        ['media:6'],
        ['media:3'],
        ['config:core.entity_view_display.node.recipe.full', 'config:filter.format.basic_html', 'node:1', 'taxonomy_term:13', 'taxonomy_term:22', 'taxonomy_term:31'],
        ['config:views.view.recipe_collections'],
        ['CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form', 'block_view', 'config:block.block.umami_search'],
        ['config:block.block.umami_account_menu', 'config:system.menu.account'],
        ['config:block.block.umami_branding', 'config:system.site'],
        ['config:block.block.umami_main_menu', 'config:system.menu.main'],
        ['config:block.block.umami_messages'],
        ['config:block.block.umami_help'],
        ['config:block.block.umami_local_tasks', 'config:workflows.workflow.editorial'],
        ['config:views.view.recipes'],
        ['config:block.block.umami_breadcrumbs'],
        ['config:block.block.umami_views_block__recipe_collections_block', 'taxonomy_term:1', 'taxonomy_term:10', 'taxonomy_term:11', 'taxonomy_term:12', 'taxonomy_term:14', 'taxonomy_term:15', 'taxonomy_term:16', 'taxonomy_term:2', 'taxonomy_term:3', 'taxonomy_term:4', 'taxonomy_term:5', 'taxonomy_term:6', 'taxonomy_term:7', 'taxonomy_term:8', 'taxonomy_term:9', 'taxonomy_term_list'],
        ['block_content:2', 'block_content_view', 'config:block.block.umami_footer_promo', 'config:core.entity_view_display.block_content.footer_promo_block.default', 'config:core.entity_view_display.media.image.medium_8_7', 'config:image.style.medium_8_7', 'file:37', 'media:19'],
        ['config:block.block.umami_footer', 'config:system.menu.footer'],
        ['config:block.block.umami_disclaimer'],
        ['block_content:1', 'config:block.block.umami_banner_home', 'config:block.block.umami_banner_recipes', 'config:block.block.umami_content', 'config:block.block.umami_languageswitcher', 'config:block.block.umami_page_title', 'config:block.block.umami_views_block__articles_aside_block_1', 'config:block.block.umami_views_block__promoted_items_block_1', 'config:block_list', 'config:configurable_language_list', 'http_response'],
        ['config:user.role.anonymous'],
      ],
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 43000,
    ];
    $this->assertMetrics($expected, $performance_data);
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
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'umamiNodePageWarmCache');
    $this->assertSession()->pageTextContains('quiche');

    $this->assertCountBetween(60, 65, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(85, 95, $performance_data->getCacheTagIsValidCount());
    $expected = [
      'QueryCount' => 172,
      'CacheGetCount' => 247,
      'CacheSetCount' => 41,
      'CacheDeleteCount' => 0,
      'CacheTagInvalidationCount' => 0,
      'CacheTagLookupQueryCount' => 28,
      'CacheTagGroupedLookups' => [
        ['entity_types', 'route_match', 'access_policies', 'routes', 'router', 'entity_field_info', 'entity_bundles', 'local_task', 'library_info'],
        ['config:views.view.related_recipes'],
        ['config:core.extension', 'views_data'],
        ['node:10', 'node:3', 'node:6', 'node:7', 'node_list'],
        ['breakpoints'],
        ['config:core.entity_view_display.media.image.responsive_3x2', 'config:image.style.large_3_2_2x', 'config:image.style.large_3_2_768x512', 'config:image.style.medium_3_2_2x', 'config:image.style.medium_3_2_600x400', 'config:responsive_image.styles.3_2_image', 'media:1', 'media_view', 'rendered'],
        ['media:21'],
        ['config:core.entity_view_display.node.recipe.card', 'node_view', 'user:6'],
        ['media:7'],
        ['media:6'],
        ['media:3'],
        ['config:core.entity_view_display.node.recipe.full', 'config:filter.format.basic_html', 'node:1', 'taxonomy_term:13', 'taxonomy_term:22', 'taxonomy_term:31'],
        ['config:views.view.recipe_collections'],
        ['CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form', 'block_view', 'config:block.block.umami_search'],
        ['config:block.block.umami_account_menu', 'config:system.menu.account'],
        ['config:block.block.umami_branding', 'config:system.site'],
        ['config:block.block.umami_main_menu', 'config:system.menu.main'],
        ['config:block.block.umami_messages'],
        ['config:block.block.umami_help'],
        ['config:block.block.umami_local_tasks', 'config:workflows.workflow.editorial'],
        ['config:views.view.recipes'],
        ['config:block.block.umami_breadcrumbs'],
        ['config:block.block.umami_views_block__recipe_collections_block', 'taxonomy_term:1', 'taxonomy_term:10', 'taxonomy_term:11', 'taxonomy_term:12', 'taxonomy_term:14', 'taxonomy_term:15', 'taxonomy_term:16', 'taxonomy_term:2', 'taxonomy_term:3', 'taxonomy_term:4', 'taxonomy_term:5', 'taxonomy_term:6', 'taxonomy_term:7', 'taxonomy_term:8', 'taxonomy_term:9', 'taxonomy_term_list'],
        ['block_content:2', 'block_content_view', 'config:block.block.umami_footer_promo', 'config:core.entity_view_display.block_content.footer_promo_block.default', 'config:core.entity_view_display.media.image.medium_8_7', 'config:image.style.medium_8_7', 'file:37', 'media:19'],
        ['config:block.block.umami_footer', 'config:system.menu.footer'],
        ['config:block.block.umami_disclaimer'],
        ['block_content:1', 'config:block.block.umami_banner_home', 'config:block.block.umami_banner_recipes', 'config:block.block.umami_content', 'config:block.block.umami_languageswitcher', 'config:block.block.umami_page_title', 'config:block.block.umami_views_block__articles_aside_block_1', 'config:block.block.umami_views_block__promoted_items_block_1', 'config:block_list', 'config:configurable_language_list', 'http_response'],
        ['config:user.role.anonymous'],
      ],
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 43000,
    ];
    $this->assertMetrics($expected, $performance_data);
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
