<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;

/**
 * Tests demo_umami profile performance.
 *
 * @group #slow
 */
class AssetAggregationAcrossPagesTest extends PerformanceTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'demo_umami';

  /**
   * Checks the asset requests made when the front and recipe pages are visited.
   */
  public function testFrontAndRecipesPages() {
    $performance_data = $this->collectPerformanceData(function () {
      $this->doRequests();
    }, 'umamiFrontAndRecipePages');
    $this->assertSame(6, $performance_data->getStylesheetCount());
    $this->assertSame(137375, $performance_data->getStylesheetBytes());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertSame(7075, $performance_data->getScriptBytes());
  }

  /**
   * Checks the asset requests made when the front and recipe pages are visited.
   */
  public function testFrontAndRecipesPagesAuthenticated() {
    $user = $this->createUser();
    $this->drupalLogin($user);
    $performance_data = $this->collectPerformanceData(function () {
      $this->doRequests();
    }, 'umamiFrontAndRecipePagesAuthenticated');
    $this->assertSame(6, $performance_data->getStylesheetCount());
    $this->assertSame(143546, $performance_data->getStylesheetBytes());
    $this->assertSame(2, $performance_data->getScriptCount());
    $this->assertSame(132083, $performance_data->getScriptBytes());
  }

  /**
   * Helper to do requests so the above test methods stay in sync.
   */
  protected function doRequests(): void {
    $this->drupalGet('<front>');
    // Give additional time for the request and all assets to be returned
    // before making the next request.
    sleep(2);
    $this->drupalGet('articles');
    sleep(2);
    $this->drupalGet('recipes');
    sleep(2);
    $this->drupalGet('recipes/deep-mediterranean-quiche');
  }

}
