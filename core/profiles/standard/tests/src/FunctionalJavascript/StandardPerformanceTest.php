<?php

declare(strict_types=1);

namespace Drupal\Tests\standard\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;
use Drupal\Tests\PerformanceData;
use Drupal\node\NodeInterface;

/**
 * Tests the performance of basic functionality in the standard profile.
 *
 * Stark is used as the default theme so that this test is not Olivero specific.
 *
 * @group Common
 */
class StandardPerformanceTest extends PerformanceTestBase {

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
  protected function setUp(): void {
    parent::setUp();

    // Grant the anonymous user the permission to look at user profiles.
    user_role_grant_permissions('anonymous', ['access user profiles']);
  }

  /**
   * Tests performance for anonymous users.
   */
  public function testAnonymous() {
    // Create two nodes to be shown on the front page.
    $this->drupalCreateNode([
      'type' => 'article',
      'promote' => NodeInterface::PROMOTED,
    ]);
    // Request a page that we're not otherwise explicitly testing to warm some
    // caches.
    $this->drupalGet('search');

    // Test frontpage.
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('');
    }, 'standardFrontPage');
    $this->assertNoJavaScript($performance_data);

    $expected_queries = [
      'SELECT "base_table"."id" AS "id", "base_table"."path" AS "path", "base_table"."alias" AS "alias", "base_table"."langcode" AS "langcode" FROM "path_alias" "base_table" WHERE ("base_table"."status" = 1) AND ("base_table"."alias" LIKE "/node" ESCAPE ' . "'\\\\'" . ') AND ("base_table"."langcode" IN ("en", "und")) ORDER BY "base_table"."langcode" ASC, "base_table"."id" DESC',
      'SELECT "name", "route", "fit" FROM "router" WHERE "pattern_outline" IN ( "/node" ) AND "number_parts" >= 1',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:user.role.anonymous" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:views.view.frontpage" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:core.extension", "views_data" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "views.view_route_names" ) AND "collection" = "state"',
      'SELECT COUNT(*) AS "expression" FROM (SELECT 1 AS "expression" FROM "node_field_data" "node_field_data" WHERE ("node_field_data"."promote" = 1) AND ("node_field_data"."status" = 1)) "subquery"',
      'SELECT "node_field_data"."sticky" AS "node_field_data_sticky", "node_field_data"."created" AS "node_field_data_created", "node_field_data"."nid" AS "nid" FROM "node_field_data" "node_field_data" WHERE ("node_field_data"."promote" = 1) AND ("node_field_data"."status" = 1) ORDER BY "node_field_data_sticky" DESC, "node_field_data_created" DESC LIMIT 10 OFFSET 0',
      'SELECT "revision"."vid" AS "vid", "revision"."langcode" AS "langcode", "revision"."revision_uid" AS "revision_uid", "revision"."revision_timestamp" AS "revision_timestamp", "revision"."revision_log" AS "revision_log", "revision"."revision_default" AS "revision_default", "base"."nid" AS "nid", "base"."type" AS "type", "base"."uuid" AS "uuid", CASE "base"."vid" WHEN "revision"."vid" THEN 1 ELSE 0 END AS "isDefaultRevision" FROM "node" "base" INNER JOIN "node_revision" "revision" ON "revision"."vid" = "base"."vid" WHERE "base"."nid" IN (1)',
      'SELECT "revision".* FROM "node_field_revision" "revision" WHERE ("revision"."nid" IN (1)) AND ("revision"."vid" IN ("1")) ORDER BY "revision"."nid" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_field_info" )',
      'SELECT "t".* FROM "node__body" "t" WHERE ("entity_id" IN (1)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "t".* FROM "node__comment" "t" WHERE ("entity_id" IN (1)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "t".* FROM "node__field_image" "t" WHERE ("entity_id" IN (1)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "t".* FROM "node__field_tags" "t" WHERE ("entity_id" IN (1)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_bundles" )',
      'SELECT "ces".* FROM "comment_entity_statistics" "ces" WHERE ("ces"."entity_id" IN (1)) AND ("ces"."entity_type" = "node")',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "node_values" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "node:1", "node_list" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "twig_extension_hash_prefix" ) AND "collection" = "state"',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "comment.type.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "node.type.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "user_values" )',
      'SELECT 1 AS "expression" FROM "path_alias" "base_table" WHERE ("base_table"."status" = 1) AND ("base_table"."path" LIKE "/node%" ESCAPE ' . "'\\\\'" . ') LIMIT 1 OFFSET 0',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "rendered", "user:0", "user_view" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:filter.format.restricted_html", "node_view" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "theme:stark" ) AND "collection" = "config.entity.key_store.block"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "block_view", "config:block.block.stark_site_branding", "config:system.site" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_search_form_narrow", "config:search.settings" )',
      'SELECT "menu_tree"."menu_name" AS "menu_name", "menu_tree"."route_name" AS "route_name", "menu_tree"."route_parameters" AS "route_parameters", "menu_tree"."url" AS "url", "menu_tree"."title" AS "title", "menu_tree"."description" AS "description", "menu_tree"."parent" AS "parent", "menu_tree"."weight" AS "weight", "menu_tree"."options" AS "options", "menu_tree"."expanded" AS "expanded", "menu_tree"."enabled" AS "enabled", "menu_tree"."provider" AS "provider", "menu_tree"."metadata" AS "metadata", "menu_tree"."class" AS "class", "menu_tree"."form_class" AS "form_class", "menu_tree"."id" AS "id" FROM "menu_tree" "menu_tree" WHERE ("route_name" = "view.frontpage.page_1") AND ("route_param_key" = "view_id=frontpage&display_id=page_1") AND ("menu_name" = "main") ORDER BY "depth" ASC, "weight" ASC, "id" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_main_menu", "config:system.menu.main" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_search_form_wide" )',
      'SELECT "menu_tree"."menu_name" AS "menu_name", "menu_tree"."route_name" AS "route_name", "menu_tree"."route_parameters" AS "route_parameters", "menu_tree"."url" AS "url", "menu_tree"."title" AS "title", "menu_tree"."description" AS "description", "menu_tree"."parent" AS "parent", "menu_tree"."weight" AS "weight", "menu_tree"."options" AS "options", "menu_tree"."expanded" AS "expanded", "menu_tree"."enabled" AS "enabled", "menu_tree"."provider" AS "provider", "menu_tree"."metadata" AS "metadata", "menu_tree"."class" AS "class", "menu_tree"."form_class" AS "form_class", "menu_tree"."id" AS "id" FROM "menu_tree" "menu_tree" WHERE ("route_name" = "view.frontpage.page_1") AND ("route_param_key" = "view_id=frontpage&display_id=page_1") AND ("menu_name" = "account") ORDER BY "depth" ASC, "weight" ASC, "id" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_account_menu", "config:system.menu.account" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_breadcrumbs" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_primary_admin_actions" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_messages" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "local_task" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_primary_local_tasks" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_secondary_local_tasks" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_help" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_powered" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_syndicate" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_content", "config:block.block.stark_page_title", "config:block_list" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "library_info" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "asset.css_js_query_string" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "http_response" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
      'INSERT INTO "semaphore" ("name", "value", "expire") VALUES ("theme_registry:runtime:stark:Drupal\Core\Utility\ThemeRegistry", "LOCK_ID", "EXPIRE")',
      'DELETE FROM "semaphore"  WHERE ("name" = "theme_registry:runtime:stark:Drupal\Core\Utility\ThemeRegistry") AND ("value" = "LOCK_ID")',
      'INSERT INTO "semaphore" ("name", "value", "expire") VALUES ("library_info:stark:Drupal\Core\Cache\CacheCollector", "LOCK_ID", "EXPIRE")',
      'DELETE FROM "semaphore"  WHERE ("name" = "library_info:stark:Drupal\Core\Cache\CacheCollector") AND ("value" = "LOCK_ID")',
      'INSERT INTO "semaphore" ("name", "value", "expire") VALUES ("path_alias_whitelist:Drupal\Core\Cache\CacheCollector", "LOCK_ID", "EXPIRE")',
      'DELETE FROM "semaphore"  WHERE ("name" = "path_alias_whitelist:Drupal\Core\Cache\CacheCollector") AND ("value" = "LOCK_ID")',
      'path_alias_select_css',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "routing.menu_masks.router" ) AND "collection" = "state"',
      'router_select_css',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "library_info" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(68, $performance_data->getQueryCount());
    $this->assertSame(137, $performance_data->getCacheGetCount());
    $this->assertSame(47, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Test node page.
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, 'standardNodePage');
    $this->assertNoJavaScript($performance_data);

    $expected_queries = [
      'SELECT "base_table"."id" AS "id", "base_table"."path" AS "path", "base_table"."alias" AS "alias", "base_table"."langcode" AS "langcode" FROM "path_alias" "base_table" WHERE ("base_table"."status" = 1) AND ("base_table"."alias" LIKE "/node/1" ESCAPE ' . "'\\\\'" . ') AND ("base_table"."langcode" IN ("en", "und")) ORDER BY "base_table"."langcode" ASC, "base_table"."id" DESC',
      'SELECT "name", "route", "fit" FROM "router" WHERE "pattern_outline" IN ( "/node/1", "/node/%", "/node" ) AND "number_parts" >= 2',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_field_info", "user_values" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "node_values" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:user.role.anonymous" )',
      'SELECT "name", "data" FROM "config" WHERE "collection" = "" AND "name" IN ( "core.entity_view_display.node.article.full" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_bundles" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "twig_extension_hash_prefix" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "rendered", "user:0", "user_view" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:filter.format.restricted_html", "node:1", "node_view" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "theme:stark" ) AND "collection" = "config.entity.key_store.block"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "block_view", "config:block.block.stark_site_branding", "config:system.site" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_search_form_narrow", "config:search.settings" )',
      'SELECT "menu_tree"."menu_name" AS "menu_name", "menu_tree"."route_name" AS "route_name", "menu_tree"."route_parameters" AS "route_parameters", "menu_tree"."url" AS "url", "menu_tree"."title" AS "title", "menu_tree"."description" AS "description", "menu_tree"."parent" AS "parent", "menu_tree"."weight" AS "weight", "menu_tree"."options" AS "options", "menu_tree"."expanded" AS "expanded", "menu_tree"."enabled" AS "enabled", "menu_tree"."provider" AS "provider", "menu_tree"."metadata" AS "metadata", "menu_tree"."class" AS "class", "menu_tree"."form_class" AS "form_class", "menu_tree"."id" AS "id" FROM "menu_tree" "menu_tree" WHERE ("route_name" = "entity.node.canonical") AND ("route_param_key" = "node=1") AND ("menu_name" = "main") ORDER BY "depth" ASC, "weight" ASC, "id" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_main_menu", "config:system.menu.main" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_search_form_wide" )',
      'SELECT "menu_tree"."menu_name" AS "menu_name", "menu_tree"."route_name" AS "route_name", "menu_tree"."route_parameters" AS "route_parameters", "menu_tree"."url" AS "url", "menu_tree"."title" AS "title", "menu_tree"."description" AS "description", "menu_tree"."parent" AS "parent", "menu_tree"."weight" AS "weight", "menu_tree"."options" AS "options", "menu_tree"."expanded" AS "expanded", "menu_tree"."enabled" AS "enabled", "menu_tree"."provider" AS "provider", "menu_tree"."metadata" AS "metadata", "menu_tree"."class" AS "class", "menu_tree"."form_class" AS "form_class", "menu_tree"."id" AS "id" FROM "menu_tree" "menu_tree" WHERE ("route_name" = "entity.node.canonical") AND ("route_param_key" = "node=1") AND ("menu_name" = "account") ORDER BY "depth" ASC, "weight" ASC, "id" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_account_menu", "config:system.menu.account" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_breadcrumbs" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_primary_admin_actions" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_messages" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "local_task" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_primary_local_tasks" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_secondary_local_tasks" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_help" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_powered" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_syndicate" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:block.block.stark_content", "config:block.block.stark_page_title", "config:block_list" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "library_info" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "asset.css_js_query_string" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "http_response" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
      'INSERT INTO "semaphore" ("name", "value", "expire") VALUES ("theme_registry:runtime:stark:Drupal\Core\Utility\ThemeRegistry", "LOCK_ID", "EXPIRE")',
      'DELETE FROM "semaphore"  WHERE ("name" = "theme_registry:runtime:stark:Drupal\Core\Utility\ThemeRegistry") AND ("value" = "LOCK_ID")',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(39, $performance_data->getQueryCount());
    $this->assertSame(95, $performance_data->getCacheGetCount());
    $this->assertSame(16, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Test user profile page.
    $user = $this->drupalCreateUser();
    $performance_data = $this->collectPerformanceData(function () use ($user) {
      $this->drupalGet('user/' . $user->id());
    }, 'standardUserPage');
    $this->assertNoJavaScript($performance_data);

    $expected_queries = [];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(41, $performance_data->getQueryCount());
    $this->assertSame(81, $performance_data->getCacheGetCount());
    $this->assertSame(16, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
  }

  /**
   * Tests the performance of logging in.
   */
  public function testLogin(): void {
    // Create a user and log them in to warm all caches. Manually submit the
    // form so that we repeat the same steps when recording performance data. Do
    // this twice so that any caches which take two requests to warm are also
    // covered.
    $account = $this->drupalCreateUser();
    foreach (range(0, 1) as $index) {
      $this->drupalGet('node');
      $this->drupalGet('user/login');
      $this->submitLoginForm($account);
      $this->drupalLogout();
    }

    $this->drupalGet('node');
    $this->drupalGet('user/login');
    $performance_data = $this->collectPerformanceData(function () use ($account) {
      $this->submitLoginForm($account);
    }, 'standardLogin');

    $expected_queries = [
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" LIKE "ACCOUNT_NAME" ESCAPE ' . "'\\\\'" . ') AND ("users_field_data"."status" = 0)',
      'SELECT COUNT(*) AS "expression" FROM (SELECT 1 AS "expression" FROM "flood" "f" WHERE ("event" = "user.failed_login_ip") AND ("identifier" = "CLIENT_IP") AND ("timestamp" > "TIMESTAMP")) "subquery"',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" IN ("ACCOUNT_NAME")) AND ("users_field_data"."status" IN (1)) AND ("users_field_data"."default_langcode" IN (1))',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_field_info", "user_values" )',
      'SELECT COUNT(*) AS "expression" FROM (SELECT 1 AS "expression" FROM "flood" "f" WHERE ("event" = "user.failed_login_user") AND ("identifier" = "CLIENT_IP") AND ("timestamp" > "TIMESTAMP")) "subquery"',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" IN ("ACCOUNT_NAME")) AND ("users_field_data"."default_langcode" IN (1))',
      'watchdog_insert',
      'UPDATE "users_field_data" SET "login"="TIMESTAMP" WHERE "uid" = "2"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'sessions_select',
      'sessions_select_expression',
      'sessions_insert',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
      'sessions_select',
      'SELECT * FROM "users_field_data" "u" WHERE "u"."uid" = "2" AND "u"."default_langcode" = 1',
      'SELECT "roles_target_id" FROM "user__roles" WHERE "entity_id" = "2"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "base"."uid" AS "uid", "base"."uuid" AS "uuid", "base"."langcode" AS "langcode" FROM "users" "base" WHERE "base"."uid" IN (2)',
      'SELECT "data".* FROM "users_field_data" "data" WHERE "data"."uid" IN (2) ORDER BY "data"."uid" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_field_info" )',
      'SELECT "t".* FROM "user__roles" "t" WHERE ("entity_id" IN (2)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "t".* FROM "user__user_picture" "t" WHERE ("entity_id" IN (2)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_bundles" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "user_values" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:user.role.authenticated" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "block_view", "config:block.block.stark_account_menu", "config:block.block.stark_breadcrumbs", "config:block.block.stark_content", "config:block.block.stark_help", "config:block.block.stark_main_menu", "config:block.block.stark_messages", "config:block.block.stark_page_title", "config:block.block.stark_powered", "config:block.block.stark_primary_admin_actions", "config:block.block.stark_account_menu"0, "config:block.block.stark_account_menu"1, "config:block.block.stark_account_menu"2, "config:block.block.stark_account_menu"3, "config:block.block.stark_account_menu"4, "config:block.block.stark_account_menu"5, "config:block.block.stark_account_menu"6, "config:block.block.stark_account_menu"7, "config:block.block.stark_account_menu"8, "config:block.block.stark_account_menu"9, "config:block.block.stark_breadcrumbs"0, "config:block.block.stark_breadcrumbs"1, "config:block.block.stark_breadcrumbs"2, "config:block.block.stark_breadcrumbs"3 )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "twig_extension_hash_prefix" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "library_info" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "asset.css_js_query_string" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'sessions_select',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "local_task" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertGreaterThanOrEqual(38, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(40, $performance_data->getQueryCount());
    $this->assertSame(64, $performance_data->getCacheGetCount());
    $this->assertSame(1, $performance_data->getCacheSetCount());
    $this->assertSame(1, $performance_data->getCacheDeleteCount());
  }

  /**
   * Tests the performance of logging in via the user login block.
   */
  public function testLoginBlock(): void {
    $this->drupalPlaceBlock('user_login_block');
    // Create a user and log them in to warm all caches. Manually submit the
    // form so that we repeat the same steps when recording performance data. Do
    // this twice so that any caches which take two requests to warm are also
    // covered.
    $account = $this->drupalCreateUser();
    $this->drupalLogout();

    foreach (range(0, 1) as $index) {
      $this->drupalGet('node');
      $this->assertSession()->responseContains('Password');
      $this->submitLoginForm($account);
      $this->drupalLogout();
    }

    $this->drupalGet('node');
    $this->assertSession()->responseContains('Password');
    $performance_data = $this->collectPerformanceData(function () use ($account) {
      $this->submitLoginForm($account);
    }, 'standardBlockLogin');

    $expected_queries = [
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:views.view.frontpage" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:core.extension", "views_data" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "views.view_route_names" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:user.role.anonymous" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "node_list" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_bundles" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "twig_extension_hash_prefix" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "theme:stark" ) AND "collection" = "config.entity.key_store.block"',
      'SELECT "config"."name" AS "name" FROM "config" "config" WHERE ("collection" = "") AND ("name" LIKE "search.page.%" ESCAPE ' . "'\\\\'" . ') ORDER BY "collection" ASC, "name" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:system.menu.account", "config:system.menu.main" )',
      'SELECT "menu_tree"."id" AS "id" FROM "menu_tree" "menu_tree" WHERE ("menu_name" = "account") AND ("expanded" = 1) AND ("has_children" = 1) AND ("enabled" = 1) AND ("parent" IN ("")) AND ("id" NOT IN (""))',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "local_task" )',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" LIKE "ACCOUNT_NAME" ESCAPE ' . "'\\\\'" . ') AND ("users_field_data"."status" = 0)',
      'SELECT COUNT(*) AS "expression" FROM (SELECT 1 AS "expression" FROM "flood" "f" WHERE ("event" = "user.failed_login_ip") AND ("identifier" = "CLIENT_IP") AND ("timestamp" > "TIMESTAMP")) "subquery"',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" IN ("ACCOUNT_NAME")) AND ("users_field_data"."status" IN (1)) AND ("users_field_data"."default_langcode" IN (1))',
      'SELECT "base"."uid" AS "uid", "base"."uuid" AS "uuid", "base"."langcode" AS "langcode" FROM "users" "base" WHERE "base"."uid" IN (2)',
      'SELECT "data".* FROM "users_field_data" "data" WHERE "data"."uid" IN (2) ORDER BY "data"."uid" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_field_info" )',
      'SELECT "t".* FROM "user__roles" "t" WHERE ("entity_id" IN (2)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "t".* FROM "user__user_picture" "t" WHERE ("entity_id" IN (2)) AND ("deleted" = 0) AND ("langcode" IN ("en", "und", "zxx")) ORDER BY "delta" ASC',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "user_values" )',
      'SELECT COUNT(*) AS "expression" FROM (SELECT 1 AS "expression" FROM "flood" "f" WHERE ("event" = "user.failed_login_user") AND ("identifier" = "CLIENT_IP") AND ("timestamp" > "TIMESTAMP")) "subquery"',
      'SELECT "base_table"."uid" AS "uid", "base_table"."uid" AS "base_table_uid" FROM "users" "base_table" INNER JOIN "users_field_data" "users_field_data" ON "users_field_data"."uid" = "base_table"."uid" WHERE ("users_field_data"."name" IN ("ACCOUNT_NAME")) AND ("users_field_data"."default_langcode" IN (1))',
      'watchdog_insert',
      'UPDATE "users_field_data" SET "login"="TIMESTAMP" WHERE "uid" = "2"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'sessions_select',
      'sessions_select_expression',
      'sessions_insert',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
      'sessions_select',
      'SELECT * FROM "users_field_data" "u" WHERE "u"."uid" = "2" AND "u"."default_langcode" = 1',
      'SELECT "roles_target_id" FROM "user__roles" WHERE "entity_id" = "2"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "route_match" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "entity_types" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.maintenance_mode" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "routes" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "config:user.role.authenticated" )',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "block_view", "config:block.block.jf547b5k", "config:block.block.stark_account_menu", "config:block.block.stark_breadcrumbs", "config:block.block.stark_content", "config:block.block.stark_help", "config:block.block.stark_main_menu", "config:block.block.stark_messages", "config:block.block.stark_page_title", "config:block.block.stark_powered", "config:block.block.jf547b5k"0, "config:block.block.jf547b5k"1, "config:block.block.jf547b5k"2, "config:block.block.jf547b5k"3, "config:block.block.jf547b5k"4, "config:block.block.jf547b5k"5, "config:block.block.jf547b5k"6, "config:block.block.jf547b5k"7, "config:block.block.jf547b5k"8, "config:block.block.jf547b5k"9, "config:block.block.stark_account_menu"0, "config:block.block.stark_account_menu"1, "config:block.block.stark_account_menu"2, "config:block.block.stark_account_menu"3, "config:block.block.stark_account_menu"4, "config:block.block.stark_account_menu"5 )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "twig_extension_hash_prefix" ) AND "collection" = "state"',
      'SELECT "tag", "invalidations" FROM "cachetags" WHERE "tag" IN ( "library_info" )',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "asset.css_js_query_string" ) AND "collection" = "state"',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "drupal.test_wait_terminate" ) AND "collection" = "state"',
      'sessions_select',
      'SELECT "name", "value" FROM "key_value" WHERE "name" IN ( "system.cron_last" ) AND "collection" = "state"',
    ];
    $recorded_queries = $performance_data->getQueries();
    $this->assertSame($expected_queries, $recorded_queries);
    $this->assertSame(49, $performance_data->getQueryCount());
    $this->assertSame(85, $performance_data->getCacheGetCount());
    $this->assertSame(1, $performance_data->getCacheSetCount());
    $this->assertSame(1, $performance_data->getCacheDeleteCount());
  }

  /**
   * Submit the user login form.
   */
  protected function submitLoginForm($account) {
    $this->submitForm([
      'name' => $account->getAccountName(),
      'pass' => $account->passRaw,
    ], 'Log in');
  }

  /**
   * Passes if no JavaScript is found on the page.
   *
   * @param Drupal\Tests\PerformanceData $performance_data
   *   A PerformanceData value object.
   *
   * @internal
   */
  protected function assertNoJavaScript(PerformanceData $performance_data): void {
    // Ensure drupalSettings is not set.
    $settings = $this->getDrupalSettings();
    $this->assertEmpty($settings, 'drupalSettings is not set.');
    $this->assertSession()->responseNotMatches('/\.js/');
    $this->assertSame(0, $performance_data->getScriptCount());
  }

  /**
   * Provides an empty implementation to prevent the resetting of caches.
   */
  protected function refreshVariables() {}

}
