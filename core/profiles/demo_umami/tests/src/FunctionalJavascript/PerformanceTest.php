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
  public function testWithOpenTelemetry(): void {
    // Chromedriver doesn't collect tracing performance logs for the very first
    // request in a test, so warm it up.
    // @todo: figure out why and remove this workaround.
    $this->drupalGet('user/login');

    $count = 0;
    while ($count < 3) {
      $count++;
      $this->doTestWithOpenTelemetry();
    }
  }

  protected function doTestWithOpenTelemetry(): void {
    // Cold cache immediately after a rebuild.
    $this->rebuildAll();
    $this->drupalGet('<front>', ['service_name' => 'FrontPageColdCache']);
    $this->assertSession()->pageTextContains('Umami');

    // Warm cache after two requests so that both back and front end caches
    // are warm.
    $this->drupalGet('<front>');
    $this->drupalGet('<front>', ['service_name' => 'FrontPageWarmCache']);
    $this->assertSession()->pageTextContains('Umami');

    // 'Lukewarm' cache, a request after a rebuild, but also after a different
    // route has been visited. This should mean that site-wide caches are warm
    // but any route-specific caches are not.
    $this->rebuildAll();
    $this->drupalGet('/user/login');
    $this->drupalGet('<front>', ['service_name' => 'FrontPageLukeWarmCache']);
    $this->assertSession()->pageTextContains('Umami');

    // Node page with a cold cache.
    $this->rebuildAll();
    $this->drupalGet('node/1', ['service_name' => 'NodePageColdCache']);
    $this->assertSession()->pageTextContains('quiche');

    // Node page with a cold cache.
    $this->rebuildAll();
    $this->drupalGet('node/1');
    $this->drupalGet('node/1', ['service_name' => 'NodePageWarmCache']);
    $this->assertSession()->pageTextContains('quiche');

    // Node page with a lukewarm cache.
    $this->rebuildAll();
    $this->drupalGet('/user/login');
    $this->drupalGet('/node/1', ['service_name' => 'NodePageLukeWarmCache']);
    $this->assertSession()->pageTextContains('quiche');
    //* @todo: add a another request, maybe 'tepid' for when a different node
    //page has already been visited but not node/1.
  }

}
