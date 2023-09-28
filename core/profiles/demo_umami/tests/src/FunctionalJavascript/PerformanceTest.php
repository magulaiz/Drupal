<?php

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\Tests\PerformanceData;
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
    $performance_data = new PerformanceData();
    $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, $performance_data);
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertSame(1, $performance_data->getScriptCount());

    $performance_data = new PerformanceData();
    $this->collectPerformanceData(function () {
      $this->drupalGet('node/1');
    }, $performance_data);
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertSame(1, $performance_data->getScriptCount());
  }

  /**
   * Load the front page as a user with access to Toolbar.
   */
  public function testFrontPagePerformance(): void {
    $admin_user = $this->drupalCreateUser(['access toolbar']);
    $this->drupalLogin($admin_user);
    $performance_data = new PerformanceData();
    $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
    }, $performance_data);
    $this->assertSession()->pageTextContains('Umami');
    $this->assertSame(2, $performance_data->getStylesheetCount());
    $this->assertSame(2, $performance_data->getScriptCount());
  }

}
