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
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    });
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $performance_data->stylesheetCount);
    $this->assertSame(1, $performance_data->scriptCount);

    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    });
    $this->assertSame(2, $performance_data->stylesheetCount);
    $this->assertSame(1, $performance_data->scriptCount);
  }

  /**
   * Load the front page as a user with access to Toolbar.
   */
  public function testFrontPagePerformance(): void {
    $admin_user = $this->drupalCreateUser(['access toolbar']);
    $this->drupalLogin($admin_user);
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    });
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $performance_data->stylesheetCount);
    $this->assertSame(2, $performance_data->scriptCount);
  }

}
