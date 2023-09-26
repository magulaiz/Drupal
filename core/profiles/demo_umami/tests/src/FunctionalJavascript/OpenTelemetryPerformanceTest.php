<?php

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

/**
 * Tests demo_umami profile performance.
 *
 * @group OpenTelemetry
 * @group #slow
 */
class OpenTelemetryPerformanceTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Logs front page tracing data with a cold cache.
   */
  public function testFrontPageColdCache() {
    $this->telemetryServiceName = FALSE;
    // @todo: Chromedriver doesn't collect tracing performance logs for the very
    // first request in a test, so warm it up.
    // @see https://www.drupal.org/project/drupal/issues/3379750
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $this->logTelemetry('umamiFrontPageColdCache', function() {
      $this->drupalGet('<front>');
    });
    $this->assertSession()->pageTextContains('Umami');
  }

  /**
   * Logs front page tracing data with a warm cache.
   */
  public function testFrontPageWarmCache() {
    // Request the page twice so that asset aggregates and image derivatives are
    // definitely cached in the browser cache. The first response builds the
    // file and serves from PHP with private, no-store headers. The second
    // request will get the file served directly from disk by the browser with
    // cacheable headers, so only the third request actually has the files
    // in the browser cache.
    $this->drupalGet('<front>');
    $this->drupalGet('<front>');
    $this->logTelemetry('umamiFrontPageWarmCache', function() {
      $this->drupalGet('<front>');
    });
  }

  /**
   * Logs front page tracing data with a lukewarm cache.
   *
   * Lukewarm here means that 'global' site caches are warm but anything
   * specific to the front page is cold.
   */
  public function testFrontPageLukewarmCache() {
    // First of all visit the front page to ensure the image style exists.
    $this->drupalGet('<front>');
    $this->rebuildAll();
    // Now visit a different page to warm non-route-specific caches.
    $this->drupalGet('/user/login');
    $this->logTelemetry('umamiFrontPageLukewarmCache', function () {
      $this->drupalGet('<front>');
    });
  }

  /**
   * Logs node page tracing data with a cold cache.
   */
  public function testNodePageColdCache() {
    // @todo: Chromedriver doesn't collect tracing performance logs for the very
    // first request in a test, so warm it up.
    // @see https://www.drupal.org/project/drupal/issues/3379750
    $this->drupalGet('user/login');
    $this->rebuildAll();
    $this->logTelemetry('umamiNodePageColdCache', function () {
      $this->drupalGet('/node/1');
    });
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Logs node page tracing data with a warm cache.
   */
  public function testNodePageWarmCache() {
    // Request the page twice so that asset aggregates are definitely cached in
    // the browser cache.
    $this->drupalGet('node/1');
    $this->drupalGet('node/1');
    $this->logTelemetry('umamiNodePageWarmCache', function () {
      $this->drupalGet('/node/1');
    });
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Logs node/1 tracing data with a lukewarm cache.
   *
   * Lukewarm here means that 'global' site caches are warm but anything
   * specific to the route or path is cold.
   */
  public function testNodePageLukeWarmCache() {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->rebuildAll();
    // Now visit a non-node page to warm non-route-specific caches.
    $this->drupalGet('/user/login');
    $this->logTelemetry('umamiNodePageLukeWarmCache', function () {
      $this->drupalGet('/node/1');
    });
    $this->assertSession()->pageTextContains('quiche');
  }

  /**
   * Log node/1 tracing data with a tepid cache.
   *
   * Tepid here means that 'global' site caches and route-specific caches are
   * warm but caches specific to this particular node/path are not.
   */
  public function testNodePageTepidCache() {
    // First of all visit the node page to ensure the image style exists.
    $this->drupalGet('node/1');
    $this->rebuildAll();
    // Now visit a different node page to warm non-path-specific caches.
    $this->drupalGet('/node/2');
    $this->logTelemetry('umamiNodePageTepidCache', function() {
      $this->drupalGet('/node/1');
    });
    $this->assertSession()->pageTextContains('quiche');
  }

}
