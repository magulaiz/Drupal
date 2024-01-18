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
    });
    $this->assertNoJavaScript($performance_data);
    // This test observes a variable number of cache operations and database
    // queries, so to avoid  random test failures, assert greater than equal
    // the highest and lowest number of observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertGreaterThanOrEqual(58, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(68, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(129, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(132, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(59, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(68, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Expected cache gets, with wildcards where necessary.
    $expected = [
      "page:http://*/:",
      "config:last_write_timestamp_cache_config",
      "data:route:[language]=en:/:",
      "config:system.site",
      "discovery:last_write_timestamp_cache_discovery",
      "discovery:entity_type",
      "config:user.role.anonymous",
      "bootstrap:last_write_timestamp_cache_bootstrap",
      "dynamic_page_cache:response:[request_format]=html:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f",
      "data:route_provider.route_load:f11ac973f5e7f2ef4a25ec2926e6a2188fa2cf33cd602e85294052143b266b7761b37d9b76bcf95c674b266ceb35d7e25fd1e42ce20068a495c4c1d40d60f083",
      "discovery:views:display",
      "config:views.view.frontpage",
      "config:system.theme",
      "config:core.extension",
      "default:core.extension.list.theme",
      "bootstrap:user_permissions_hash:anonymous",
      "render:view:frontpage:display:page_1:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "discovery:element_info_build:stark",
      "config:views.settings",
      "data:views:unpack_options:2a50e953655914dc23295e41512f26b0ce5607cb1a329b1e86da253848b80fdb:en",
      "data:views:unpack_options:f11a2f020a80df60d05bcd5a53d0dc32d5bc1306c31ebf30b6dcc8a84d8eb98d:en",
      "discovery:views:access",
      "bootstrap:hook_info",
      "default:views_data:node_field_data:en",
      "default:views_data:en",
      "discovery:views:sort",
      "discovery:views:filter",
      "default:views_data:views:en",
      "discovery:views:area",
      "default:views_data:node:en",
      "discovery:views:pager",
      "discovery:views:query",
      "discovery:views:style",
      "discovery:views:row",
      "data:views:unpack_options:b4e629fd7363f3930700d12f5a1f2ad07fec54787e76a7caa539ff0eeec2882b:en",
      // @todo this is not always present?
      // "bootstrap:path_alias_whitelist",
      "discovery:views:cache",
      "data:frontpage:page_1:results:*",
      "discovery:entity_type_definitions.installed",
      "discovery:node.field_storage_definitions.installed",
      "entity:values:node:1",
      "discovery:entity_base_field_definitions:node:en",
      "discovery:entity_bundle_field_definitions:node:article:en",
      "discovery:entity_field_storage_definitions:node:en",
      "discovery:field_types_plugins",
      "discovery:typed_data_types_plugins",
      "discovery:entity_bundle_info:en",
      "config:user.settings",
      "discovery:entity_field_map",
      "discovery:views:exposed_form",
      "bootstrap:theme_registry:runtime:stark",
      "discovery:entity_view_mode_info:en",
      "render:view:frontpage:display:page_1:[languages:language_interface]=en:[theme]=stark:[url.query_args]=:[user.node_grants:view]=view.all:[user.permissions]=*",
      "default:theme_registry:stark",
      "render:entity_view:node:1:teaser:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "config:core.entity_view_display.node.article.teaser",
      "config:core.entity_view_display.node.article.default",
      "discovery:entity_extra_field_info:en",
      "config:comment.type.comment",
      "config:node.type.article",
      "config:node.type.page",
      "discovery:field_formatter_types_plugins",
      "discovery:entity_base_field_definitions:user:en",
      "discovery:user.field_storage_definitions.installed",
      "entity:values:user:0",
      "discovery:entity_bundle_field_definitions:user:user:en",
      "config:core.date_format.medium",
      "config:core.date_format.long",
      "config:system.theme.global",
      // Not always present?
      // "config:stark.settings",
      "render:entity_view:user:0:compact:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "config:core.entity_view_display.user.user.compact",
      "config:core.entity_view_display.user.user.default",
      "discovery:entity_field_storage_definitions:user:en",
      "render:entity_view:user:0:compact:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "config:filter.format.restricted_html",
      "discovery:filter_plugins",
      "config:system.image",
      "discovery:image_toolkit_plugins",
      "config:user.role.authenticated",
      "render:entity_view:node:1:teaser:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:view:frontpage:display:page_1:[languages:language_interface]=en:[theme]=stark:[timezone]=Australia/Sydney:[url.query_args]=:[user.node_grants:view]=view.all:[user.permissions]=*",
      "render:view:frontpage:display:page_1:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "discovery:variant_plugins",
      "config:block.block.stark_account_menu",
      "config:block.block.stark_breadcrumbs",
      "config:block.block.stark_content",
      "config:block.block.stark_help",
      "config:block.block.stark_main_menu",
      "config:block.block.stark_messages",
      "config:block.block.stark_page_title",
      "config:block.block.stark_powered",
      "config:block.block.stark_primary_admin_actions",
      "config:block.block.stark_primary_local_tasks",
      "config:block.block.stark_search_form_narrow",
      "config:block.block.stark_search_form_wide",
      "config:block.block.stark_secondary_local_tasks",
      "config:block.block.stark_site_branding",
      "config:block.block.stark_syndicate",
      "discovery:block_plugins",
      "render:entity_view:block:stark_site_branding:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_search_form_narrow:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      'menu:active-trail:route:view.frontpage.page_1:route_parameters:a:2:{s:10:"display_id";s:6:"page_1";s:7:"view_id";s:9:"frontpage";}',
      "render:entity_view:block:stark_main_menu:[languages:language_interface]=en:[route.menu_active_trails:main]=menu_trail.main|:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_search_form_wide:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_account_menu:[languages:language_interface]=en:[route.menu_active_trails:account]=menu_trail.account|:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_account_menu:[languages:language_interface]=en:[route.menu_active_trails:account]=menu_trail.account|:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_breadcrumbs:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_breadcrumbs:[languages:language_interface]=en:[theme]=stark:[url.path.is_front]=is_front.1:[url.path.parent]=:[user.permissions]=*",
      "render:entity_view:block:stark_breadcrumbs:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_breadcrumbs:[languages:language_interface]=en:[theme]=stark:[url.path.is_front]=is_front.1:[url.path.parent]=:[user.permissions]=*",
      "render:entity_view:block:stark_primary_admin_actions:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_primary_admin_actions:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "discovery:local_action_plugins:en",
      "render:entity_view:block:stark_primary_admin_actions:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_primary_admin_actions:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_messages:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_primary_local_tasks:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_primary_local_tasks:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "discovery:local_task_plugins:en:view.frontpage.page_1",
      "discovery:local_task_plugins:en",
      "render:entity_view:block:stark_primary_local_tasks:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_primary_local_tasks:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_secondary_local_tasks:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_secondary_local_tasks:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_secondary_local_tasks:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_secondary_local_tasks:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_help:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_help:[languages:language_interface]=en:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_powered:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "render:entity_view:block:stark_syndicate:[languages:language_interface]=en:[theme]=stark:[user.permissions]=*",
      "dynamic_page_cache:response:[request_format]=html:[route]=view.frontpage.page_19c08cb355eb3b07aca692af8c68cc11a934f5e596bf92a0449a69ff3afcd6d3f",
      "discovery:library_info:stark",
      "data:css:stark:en_ydG1CE-tcFGGQAdMJGsFxp0bIDCn8rm15xRWVYArTU1",
      "data:js:stark:en:_ydG1CE-tcFGGQAdMJGsFxp0bIDCn8rm15xRWVYArTU11",
      "bootstrap:theme_registry:runtime:stark",
      "bootstrap:path_alias_whitelist",
      "page:http://*/sites/simpletest/*/files/css/css_*.css?delta=0&language=en&theme=stark&include=eJwrriwuSc3VT0osTtUpy0wtL9YHk3q5-SmlOakAuE0L2w:",
      "config:last_write_timestamp_cache_config",
      "data:route:[language]=en:/sites/simpletest/*/files/css/css_*.css:delta=0&include=eJwrriwuSc3VT0osTtUpy0wtL9YHk3q5-SmlOakAuE0L2w&language=en&theme=stark",
      "dynamic_page_cache:response:[request_format]=html:[route]=system.css_asset*",
      "bootstrap:last_write_timestamp_cache_bootstrap",
      "bootstrap:routing.non_admin_routes",
      "data:route_provider.route_load:f11ac973f5e7f2ef4a25ec2926e6a2188fa2cf33cd602e85294052143b266b7761b37d9b76bcf95c674b266ceb35d7e25fd1e42ce20068a495c4c1d40d60f083",
      "bootstrap:theme.active_theme.stark",
      "discovery:last_write_timestamp_cache_discovery",
      // @todo Sometimes this appears earlier?
      // "discovery:library_info:stark",
      "data:css:stark:en_ydG1CE-tcFGGQAdMJGsFxp0bIDCn8rm15xRWVYArTU0",
      "bootstrap:module_implements",
    ];
    $actual = $performance_data->getCacheGets();
    $key = 0;
    foreach ($expected as $value) {
      $actual = array_slice($actual, $key);
      $matches = preg_grep('/' . str_replace('\*', '.*', preg_quote($value, '/')) . '/', $actual);
      $this->assertNotEmpty($matches, "Expected to find $value in " . print_r($actual, TRUE));
      $key = array_key_first($matches);
    }

    // Test node page.
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    });
    $this->assertNoJavaScript($performance_data);

    // This test observes a variable number of cache operations and database
    // queries, so to avoid  random test failures, assert greater than equal
    // the highest and lowest number of observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610

    $this->assertGreaterThanOrEqual(38, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(39, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(87, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(88, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(20, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(28, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Test user profile page.
    $user = $this->drupalCreateUser();
    $performance_data = $this->collectPerformanceData(function () use ($user) {
      $this->drupalGet('user/' . $user->id());
    });
    $this->assertNoJavaScript($performance_data);
    $this->assertSame(40, $performance_data->getQueryCount());

    // This test observes a variable number of cache gets and sets, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertGreaterThanOrEqual(74, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(80, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(19, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(27, $performance_data->getCacheSetCount());
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
    });

    // This test observes a variable number of database queries, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertLessThanOrEqual(42, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(38, $performance_data->getQueryCount());
    $this->assertSame(28, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(2, $performance_data->getCacheSetCount());
    $this->assertGreaterThanOrEqual(1, $performance_data->getCacheSetCount());
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
    });
    $this->assertLessThanOrEqual(51, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(47, $performance_data->getQueryCount());
    // This test observes a variable number of cache operations, so to avoid random
    // test failures, assert greater than equal the highest and lowest number
    // observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertLessThanOrEqual(32, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(30, $performance_data->getCacheGetCount());

    $this->assertLessThanOrEqual(4, $performance_data->getCacheSetCount());
    $this->assertGreaterThanOrEqual(1, $performance_data->getCacheSetCount());
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
