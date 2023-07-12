<?php

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

/**
 * Tests demo_umami profile performance.
 *
 * @group Performance
 */
class PerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Just load the front page.
   */
  public function testPagesAnonymous(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $this->stylesheetCount);
    $this->assertSame(1, $this->scriptCount);

    $this->drupalGet('node/1');
    $this->assertSame(2, $this->stylesheetCount);
    $this->assertSame(1, $this->scriptCount);
  }

  /**
   * Load the front page as a user with access to Toolbar.
   */
  public function testFrontPagePerformance(): void {
    $admin_user = $this->drupalCreateUser(['access toolbar']);
    $this->drupalLogin($admin_user);
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $this->stylesheetCount);
    $this->assertSame(2, $this->scriptCount);
  }

  /**
   * Log front page tracing data with a cold cache.
   */
  public function testFrontPageColdCache() {
    $this->sendTelemetry = TRUE;
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Umami');
  }

  /**
   * Log front page tracing data with a warm cache.
   */
  public function testFrontPageWarmCache() {
    $this->sendTelemetry = FALSE;
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('<front>');
    $this->drupalGet('<front>');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('<front>');
  }

  /**
   * Log front page tracing data with a lukewarm cache.
   *
   * Lukewarm here means that 'global' site caches are warm but anything
   * specific to the front page is cold.
   */
  public function testFrontPageLukeWarmCache() {
    $this->sendTelemetry = FALSE;
    $this->drupalGet('/user/login');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('<front>');
  }

  /**
   * Log node page tracing data with a cold cache.
   */
  public function testNodePageColdCache() {
    $this->sendTelemetry = TRUE;
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node page tracing data with a warm cache.
   */
  public function testNodePageWarmCache() {
    $this->sendTelemetry = FALSE;
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('node/1');
    $this->drupalGet('node/1');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('node/1');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node/1 tracing data with a lukewarm cache.
   *
   * Lukewarm here means that 'global' site caches are warm but anything
   * specific to the front page is cold.
   */
  public function testNodePageLukeWarmCache() {
    $this->sendTelemetry = FALSE;
    $this->drupalGet('/user/login');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('quiche');
  }

}
