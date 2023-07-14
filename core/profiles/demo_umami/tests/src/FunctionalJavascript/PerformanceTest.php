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
   *
   * @group OpenTelemetry
   */
  public function testFrontPageColdCache() {
    $this->sendTelemetry = FALSE;
    // Chromedriver doesn't collect tracing performance logs for the very first
    // request in a test, so warm it up.
    // @todo: figure out why and remove this workaround.
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $this->sendTelemetry = TRUE;
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Umami');
  }

  /**
   * Log front page tracing data with a warm cache.
   *
   * @group OpenTelemetry
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
   *
   * @group OpenTelemetry
   */
  public function testFrontPageLukeWarmCache() {
    $this->sendTelemetry = FALSE;
    $this->drupalGet('/user/login');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('<front>');
  }

  /**
   * Log node page tracing data with a cold cache.
   *
   * @group OpenTelemetry
   */
  public function testNodePageColdCache() {
    $this->sendTelemetry = FALSE;
    // Chromedriver doesn't collect tracing performance logs for the very first
    // request in a test, so warm it up.
    // @todo: figure out why and remove this workaround.
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $this->sendTelemetry = TRUE;
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node page tracing data with a warm cache.
   *
   * @group OpenTelemetry
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
   * specific to the page is cold.
   *
   * @todo: add a another method, maybe 'tepid' for when a different node page
   * has already been visited but not node/1.
   *
   * @group OpenTelemetry
   */
  public function testNodePageLukeWarmCache() {
    $this->sendTelemetry = FALSE;
    $this->drupalGet('/user/login');
    $this->sendTelemetry = TRUE;
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('quiche');
  }

}
