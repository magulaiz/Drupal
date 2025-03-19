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
class OpenTelemetryFrontPagePerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Tests performance of the Umami demo front page.
   */
  public function testFrontPagePerformance(): void {
    $this->testFrontPageColdCache();
    $this->testFrontPageCoolCache();
    $this->testFrontPageHotCache();
  }

  /**
   * Logs front page tracing data with a cold cache.
   */
  protected function testFrontPageColdCache(): void {
    // @todo Chromedriver doesn't collect tracing performance logs for the very
    //   first request in a test, so warm it up.
    //   https://www.drupal.org/project/drupal/issues/3379750
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'umamiFrontPageColdCache');
    $this->assertSession()->pageTextContains('Umami');

    $this->assertCountBetween(245, 255, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(35, 85, $performance_data->getCacheTagIsValidCount());
    $this->assertCountBetween(340, 380, $performance_data->getQueryCount());
    $this->assertCountBetween(630, 760, $performance_data->getCacheGetCount());
    $this->assertCountBetween(415, 435, $performance_data->getCacheSetCount());
    $this->assertCountBetween(1, 2, $performance_data->getCacheDeleteCount());
    $this->assertCountBetween(55, 65, $performance_data->getCacheTagLookupQueryCount());
    $expected = [
      'CacheTagInvalidationCount' => 0,
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 41000,
    ];
    $this->assertMetrics($expected, $performance_data);
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

    $expected = [
      'QueryCount' => 0,
      'CacheGetCount' => 1,
      'CacheSetCount' => 0,
      'CacheDeleteCount' => 0,
      'CacheTagInvalidationCount' => 0,
      'CacheTagLookupQueryCount' => 1,
      'ScriptCount' => 1,
      'ScriptBytes' => 11850,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 40000,
    ];
    $this->assertMetrics($expected, $performance_data);
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
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, 'umamiFrontPageCoolCache');

    $this->assertCountBetween(80, 90, $performance_data->getCacheTagChecksumCount());
    $this->assertCountBetween(83, 90, $performance_data->getCacheTagIsValidCount());
    $expected = [
      'QueryCount' => 106,
      'CacheGetCount' => 285,
      'CacheSetCount' => 90,
      'CacheDeleteCount' => 0,
      'CacheTagInvalidationCount' => 0,
      'CacheTagLookupQueryCount' => 34,
      'CacheTagGroupedLookups' => [
        ['entity_types', 'route_match', 'access_policies', 'routes', 'router', 'entity_field_info', 'entity_bundles', 'local_task', 'library_info'],
        ['config:views.view.frontpage'],
        ['config:core.extension', 'views_data'],
        ['node:10', 'node:7', 'node:8', 'node:9', 'node_list'],
        ['breakpoints'],
        ['config:core.entity_view_display.media.image.responsive_3x2', 'config:image.style.large_3_2_2x', 'config:image.style.large_3_2_768x512', 'config:image.style.medium_3_2_2x', 'config:image.style.medium_3_2_600x400', 'config:responsive_image.styles.3_2_image', 'media:21', 'media_view', 'rendered'],
        ['config:core.entity_view_display.node.recipe.card_common', 'node_view', 'user:6'],
        ['media:9'],
        ['media:8'],
        ['media:7'],
        ['config:filter.format.full_html'],
        ['config:views.view.promoted_items'],
        ['config:views.view.recipe_collections'],
        ['CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form', 'block_view', 'config:block.block.umami_search'],
        ['config:block.block.umami_account_menu', 'config:system.menu.account'],
        ['config:block.block.umami_branding', 'config:system.site'],
        ['config:block.block.umami_main_menu', 'config:system.menu.main'],
        ['config:block.block.umami_messages'],
        ['config:block.block.umami_help'],
        ['config:block.block.umami_local_tasks'],
        ['config:core.entity_view_display.media.image.scale_crop_7_3_large', 'config:image.style.scale_crop_7_3_large', 'config:image.style.scale_crop_7_3_medium', 'config:image.style.scale_crop_7_3_tiny', 'config:image.style.scale_crop_7_3_wide', 'config:responsive_image.styles.hero', 'media:18'],
        ['block_content:3', 'block_content_view', 'config:block.block.umami_banner_home', 'config:core.entity_view_display.block_content.banner_block.default'],
        ['node:18'],
        ['media:17'],
        ['config:core.entity_view_display.node.article.card_common'],
        ['config:core.entity_view_display.media.image.square', 'config:image.style.square_large', 'config:image.style.square_medium', 'config:image.style.square_small', 'config:responsive_image.styles.square'],
        ['config:core.entity_view_display.node.recipe.card_common_alt'],
        ['config:block.block.umami_views_block__promoted_items_block_1'],
        ['config:block.block.umami_views_block__recipe_collections_block', 'taxonomy_term:1', 'taxonomy_term:10', 'taxonomy_term:11', 'taxonomy_term:12', 'taxonomy_term:13', 'taxonomy_term:14', 'taxonomy_term:15', 'taxonomy_term:16', 'taxonomy_term:2', 'taxonomy_term:3', 'taxonomy_term:4', 'taxonomy_term:5', 'taxonomy_term:6', 'taxonomy_term:7', 'taxonomy_term:8', 'taxonomy_term:9', 'taxonomy_term_list'],
        ['block_content:2', 'config:block.block.umami_footer_promo', 'config:core.entity_view_display.block_content.footer_promo_block.default', 'config:core.entity_view_display.media.image.medium_8_7', 'config:image.style.medium_8_7', 'file:37', 'media:19'],
        ['config:block.block.umami_footer', 'config:system.menu.footer'],
        ['config:block.block.umami_disclaimer', 'config:filter.format.basic_html'],
        ['block_content:1', 'config:block.block.umami_banner_recipes', 'config:block.block.umami_breadcrumbs', 'config:block.block.umami_content', 'config:block.block.umami_languageswitcher', 'config:block.block.umami_page_title', 'config:block.block.umami_views_block__articles_aside_block_1', 'config:block_list', 'config:configurable_language_list', 'http_response'],
        ['config:user.role.anonymous'],
      ],
      'ScriptCount' => 1,
      'ScriptBytes' => 12000,
      'StylesheetCount' => 2,
      'StylesheetBytes' => 41000,
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
