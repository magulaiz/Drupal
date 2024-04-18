<?php

declare(strict_types=1);

namespace Drupal\Tests\demo_umami\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\PerformanceTestBase;
use Drupal\Tests\PerformanceData;

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
    $performance_data = $this->doRequests();
    $this->assertSame(4, $performance_data->getStylesheetCount());
    $this->assertLessThan(82500, $performance_data->getStylesheetBytes());
    $this->assertSame(1, $performance_data->getScriptCount());
    $this->assertLessThan(7500, $performance_data->getScriptBytes());
  }

  /**
   * Checks the asset requests made when the front and recipe pages are visited.
   */
  public function testFrontAndRecipesPagesAuthenticated() {
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->rebuildAll();
    $performance_data = $this->doRequests();
    $this->assertSame(4, $performance_data->getStylesheetCount());
<<<<<<< HEAD
<<<<<<< HEAD
    $this->assertLessThan(89500, $performance_data->getStylesheetBytes());
    $this->assertSame(2, $performance_data->getScriptCount());
    $this->assertLessThan(250000, $performance_data->getScriptBytes());
=======
=======
>>>>>>> 33667d7883 (Issue #3441844 by catch: Set budgets rather than exact numbers of asset size assertions)
    $this->assertSame(89167, $performance_data->getStylesheetBytes());
    $this->assertSame(1, $performance_data->getScriptCount());
<<<<<<< HEAD
    $this->assertSame(132083, $performance_data->getScriptBytes());
>>>>>>> 0f7d442276 (Update assertions to reflect less file downloads.)
=======
    $this->assertSame(132067, $performance_data->getScriptBytes());
<<<<<<< HEAD
>>>>>>> 01db883bf5 (Fix merge conflicts.)
=======
=======
    $this->assertLessThan(89500, $performance_data->getStylesheetBytes());
    $this->assertSame(2, $performance_data->getScriptCount());
    $this->assertLessThan(264500, $performance_data->getScriptBytes());
>>>>>>> 05e4d1a843 (Issue #3441844 by catch: Set budgets rather than exact numbers of asset size assertions)
>>>>>>> 33667d7883 (Issue #3441844 by catch: Set budgets rather than exact numbers of asset size assertions)
  }

  /**
   * Helper to do requests so the above test methods stay in sync.
   */
  protected function doRequests(): PerformanceData {
    $performance_data = $this->collectPerformanceData(function () {
      $this->drupalGet('<front>');
      // Give additional time for the request and all assets to be returned
      // before making the next request.
      sleep(2);
      $this->drupalGet('articles');
    }, 'umamiFrontAndRecipePages');
    return $performance_data;
  }

}
