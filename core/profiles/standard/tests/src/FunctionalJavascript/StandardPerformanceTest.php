<?php

namespace Drupal\Tests\standard\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;
use Drupal\Tests\PerformanceData;
use Drupal\node\NodeInterface;

/**
 * Tests that anonymous users are not served any JavaScript.
 *
 * This is tested with the core modules that are enabled in the 'standard'
 * profile.
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
   * Tests that anonymous users are not served any JavaScript.
   */
  public function testNoJavaScript() {
    // Create a node of content type 'article' that is listed on the frontpage.
    $this->drupalCreateNode([
      'type' => 'article',
      'promote' => NodeInterface::PROMOTED,
    ]);

    // Test frontpage.
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('');
    });
    $this->assertNoJavaScript($performance_data);
    // This test observes a variable number of database queries, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertGreaterThanOrEqual(101, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(113, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(194, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(207, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(174, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(177, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Test node page.
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    });
    $this->assertNoJavaScript($performance_data);
    // This test observes a variable number of database queries, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertGreaterThanOrEqual(46, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(46, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(107, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(107, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(25, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(25, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());

    // Test user profile page.
    $user = $this->drupalCreateUser();
    $performance_data = $this->collectPerformanceData(function () use ($user) {
      $this->drupalGet('user/' . $user->id());
    });
    $this->assertNoJavaScript($performance_data);
    // This test observes a variable number of database queries, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertGreaterThanOrEqual(40, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(40, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(93, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(94, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(93, $performance_data->getCacheSetCount());
    $this->assertLessThanOrEqual(94, $performance_data->getCacheSetCount());
    $this->assertSame(0, $performance_data->getCacheDeleteCount());
  }

  /**
   * Tests the performance of logging in.
   */
  public function testLogin(): void {
    // Create a user and log them in to warm all caches.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
    $this->drupalLogout();

    $this->drupalGet('node');
    $this->drupalGet('user/login');
    $performance_data = $this->collectPerformanceData(function () use ($account) {
      $this->submitForm([
        'name' => $account->getAccountName(),
        'pass' => $account->passRaw,
      ], 'Log in');
    });
    // This test observes a variable number of database queries, so to avoid
    // random test failures, assert greater than equal the highest and lowest
    // number of queries observed during test runs.
    // See https://www.drupal.org/project/drupal/issues/3402610
    $this->assertLessThanOrEqual(41, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(40, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(44, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(43, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(2, $performance_data->getCacheSetCount());
    $this->assertGreaterThanOrEqual(2, $performance_data->getCacheSetCount());
    $this->assertSame(1, $performance_data->getCacheDeleteCount());
  }

  /**
   * Tests the performance of logging in via the user login block.
   */
  public function testLoginBlock(): void {
    $this->drupalPlaceBlock('user_login_block');
    // Create a user and log them in to warm all caches.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
    $this->drupalLogout();

    $this->drupalGet('node');
    $this->assertSession()->responseContains('Password');
    $performance_data = $this->collectPerformanceData(function () use ($account) {
      $this->submitForm([
        'name' => $account->getAccountName(),
        'pass' => $account->passRaw,
      ], 'Log in');
    });
    $this->assertLessThanOrEqual(76, $performance_data->getQueryCount());
    $this->assertGreaterThanOrEqual(75, $performance_data->getQueryCount());
    $this->assertLessThanOrEqual(126, $performance_data->getCacheGetCount());
    $this->assertGreaterThanOrEqual(100, $performance_data->getCacheGetCount());
    $this->assertLessThanOrEqual(100, $performance_data->getCacheSetCount());
    $this->assertGreaterThanOrEqual(4, $performance_data->getCacheSetCount());
    $this->assertSame(1, $performance_data->getCacheDeleteCount());
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
